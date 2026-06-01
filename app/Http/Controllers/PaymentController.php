<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class PaymentController extends Controller
{
    private $publicKey;
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        //CONECTADO: Leemos desde config() para evitar errores de caché en producción
        $this->publicKey = config('services.mercadopago.public_key');
        $this->accessToken = config('services.mercadopago.access_token');
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Create a MercadoPago preference for the payment
     */
    public function createPreference(Request $request)
    {
        try {
            // Validar los datos entrantes de la tienda
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.title' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'payer' => 'required|array',
                'payer.name' => 'required|string',
                'payer.email' => 'required|email',
                'payer.phone' => 'required|string',
                'payer.address' => 'required|string',
            ]);

            // 🛒 1. Calcular el total del carrito localmente
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Cálculo del impuesto (10%)
            $tax = $total * 0.10;
            $totalWithTax = $total + $tax;

            // 2. Crear la Orden en tu Base de Datos local (Estado inicial: pendiente)
            $order = Order::create([
                'user_id' => auth()->id() ?? 1, // Usuario logueado o ID 1 por defecto para pruebas
                'total' => $totalWithTax,
                'status' => 'pendiente',
            ]);

            // 3. Preparar los datos de la preferencia para MercadoPago
            $preferenceData = [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'],
                        'quantity' => intval($item['quantity']),
                        'unit_price' => floatval($item['unit_price']),
                        'currency_id' => 'PEN' // Moneda local: Soles peruanos
                    ];
                }, $validated['items']),
                'payer' => [
                    'name' => $validated['payer']['name'],
                    'email' => $validated['payer']['email'],
                    'phone' => [
                        'area_code' => '51', // Prefijo de Perú
                        'number' => str_replace(['+', ' ', '-'], '', $validated['payer']['phone']),
                    ],
                    'address' => [
                        'street_name' => $validated['payer']['address'],
                    ],
                ],
                'back_urls' => [
                    'success' => 'https://funko.blog/?status=success',
                    'pending' => 'https://funko.blog/?status=pending',
                    'failure' => 'https://funko.blog/?status=failure',
                ],
                'auto_return' => 'approved',
                //Vinculamos el ID de nuestra tabla 'orders' con MercadoPago
                'external_reference' => strval($order->id),
                'notification_url' => 'https://funko.blog/api/payment/webhook',
            ];

            // Crear la preferencia en la API de MercadoPago
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/checkout/preferences", $preferenceData);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to create MercadoPago preference',
                    'details' => $response->json(),
                ], 422);
            }

            $preference = $response->json();

            return response()->json([
                'success' => true,
                'preference_id' => $preference['id'],
                'init_point' => $preference['init_point'],
                'sandbox_init_point' => $preference['sandbox_init_point'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment preference creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle MercadoPago webhook notifications (IPN & Webhooks V2)
     */
    public function handleWebhook(Request $request)
    {
        try {
            // SOPORTE DÚO: Detectar si el ID viene por los parámetros URL o por el cuerpo JSON
            $type = $request->query('type') ?? $request->input('type');
            $id = $request->query('id') ?? $request->input('data.id');

            if ($type === 'payment' && $id) {
                // Consultar el estado real del pago directamente a los servidores de MercadoPago
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    
                    $status = $payment['status']; // approved, pending, rejected
                    $orderId = $payment['external_reference']; // El ID de nuestra orden local

                    Log::info("MercadoPago Webhook recibido. Orden: {$orderId}, Estado: {$status}");

                    // ACTUALIZACIÓN AUTOMÁTICA EN BASE DE DATOS
                    $order = Order::find($orderId);
                    if ($order) {
                        if ($status === 'approved') {
                            $order->update([
                                'status' => 'aprobado',
                                'mp_payment_id' => $id
                            ]);
                            Log::info("Orden #{$orderId} marcada como APROBADA.");
                        } elseif ($status === 'rejected') {
                            $order->update([
                                'status' => 'rechazado',
                                'mp_payment_id' => $id
                            ]);
                            Log::info("Orden #{$orderId} marcada como RECHAZADA.");
                        }
                    }
                }
            }

            // 🌟OBLIGATORIO: Responder 200 a MercadoPago para que sepa que procesamos la alerta
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Payment success callback
     */
    public function success(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Payment pending callback
     */
    public function pending(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment is pending approval',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Payment failure callback
     */
    public function failure(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment was declined',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Get public key for frontend
     */
    public function getPublicKey()
    {
        return response()->json([
            'public_key' => $this->publicKey,
        ]);
    }
}