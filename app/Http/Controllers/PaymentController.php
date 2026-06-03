<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Product; // Asegúrate de importar el modelo Product

class PaymentController extends Controller
{
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Crea una "Preferencia" de Checkout Pro y devuelve el link de pago
     */
    public function createPreference(Request $request)
    {
        try {
            // 1. Ahora validamos que nos llegue un array de items (el carrito real)
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.title' => 'required|string',
                'items.*.quantity' => 'required|integer',
                'items.*.price' => 'required|numeric',
            ]);

            // 2. Calculamos el total real sumando los items
            $total = 0;
            $mpItems = [];
            
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['price'];
                
                // Formateamos los items para Mercado Pago
                $mpItems[] = [
                    'id' => strval($item['id']),
                    'title' => $item['title'],
                    'quantity' => intval($item['quantity']),
                    'currency_id' => 'PEN',
                    'unit_price' => floatval($item['price'])
                ];
            }

            // 3. Creamos la orden. 
            // Guardamos el carrito como JSON en la orden para saber qué descontar luego.
            // (Asegúrate de que tu tabla orders tenga una columna 'cart_data' de tipo json o text, o ajusta esto si usas una tabla intermedia order_items)
            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'total' => $total,
                'status' => 'pendiente',
                // Guardamos los items temporalmente en la orden
                'cart_data' => json_encode($validated['items']) 
            ]);

            // 4. Armamos la preferencia para Mercado Pago
            $preferenceData = [
                'items' => $mpItems,
                'back_urls' => [
                    'success' => 'https://funko.blog/cart?status=success',
                    'failure' => 'https://funko.blog/cart?status=failure',
                    'pending' => 'https://funko.blog/cart?status=pending'
                ],
                'auto_return' => 'approved',
                'external_reference' => strval($order->id),
                // URL donde MP nos avisará silenciosamente que el pago se aprobó
                'notification_url' => 'https://funko.blog/api/payment/webhook' 
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/checkout/preferences", $preferenceData);

            if ($response->failed()) {
                Log::error('Error creando preferencia MP:', $response->json());
                return response()->json(['error' => 'Fallo al conectar con Mercado Pago'], 500);
            }

            return response()->json([
                'success' => true,
                // AQUÍ ESTÁ EL CAMBIO A PRODUCCIÓN:
                'init_point' => $response->json()['init_point'] 
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción en createPreference', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * El Webhook: Mercado Pago llama a esta función cuando el cliente paga.
     * Aquí es donde RESTAMOS EL STOCK.
     */
    public function handleWebhook(Request $request)
    {
        try {
            $type = $request->query('type') ?? $request->input('type');
            $id = $request->query('data_id') ?? $request->query('id') ?? $request->input('data.id');

            if ($type === 'payment' && $id) {
                // Consultamos el estado real del pago a Mercado Pago
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    $status = $payment['status'];
                    $orderId = $payment['external_reference'];

                    $order = Order::find($orderId);
                    
                    // Solo procedemos si la orden existe y AÚN NO ha sido aprobada
                    if ($order && $order->status !== 'aprobado' && $status === 'approved') {
                        
                        $order->update(['status' => 'aprobado']);

                        // ¡LA MAGIA DEL STOCK! 
                        // Leemos el carrito que guardamos y restamos las cantidades
                        if ($order->cart_data) {
                            $items = json_decode($order->cart_data, true);
                            foreach ($items as $item) {
                                Product::where('id', $item['id'])
                                        ->decrement('stock', $item['quantity']);
                            }
                        }
                        
                        Log::info("Orden #{$orderId} aprobada. Stock descontado.");
                    }
                }
            }
            // Siempre debemos responder con 200 OK rápido para que MP no reintente
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error en Webhook:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}