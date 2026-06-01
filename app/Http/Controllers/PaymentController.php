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
        // Inicializacion de credenciales desde la configuracion de servicios 
        // para prevenir conflictos generados por la cache en el entorno de produccion.
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
            // Validacion de la estructura de datos entrante desde el cliente
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.title' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'payer' => 'required|array',
                'payer.name' => 'required|string',
                'payer.email' => 'required|email',
            ]);

            // Calculo del monto subtotal iterando sobre los elementos del carrito
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Adicion de la carga impositiva del 10%
            $tax = $total * 0.10;
            $totalWithTax = $total + $tax;

            // Generacion del registro persistente de la orden en estado inicial
            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'total' => $totalWithTax,
                'status' => 'pendiente',
            ]);

            // Estructuracion del payload requerido por la API de Preferencias
            $preferenceData = [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'],
                        'quantity' => intval($item['quantity']),
                        'unit_price' => floatval($item['unit_price']),
                        'currency_id' => 'PEN' 
                    ];
                }, $validated['items']),
                
                // Definicion del pagador para cumplir con los requerimientos basicos de MercadoPago
                'payer' => [
                    'name' => $validated['payer']['name'],
                    'email' => $validated['payer']['email'],
                ],
                
                'back_urls' => [
                    'success' => 'https://funko.blog/?status=success',
                    'pending' => 'https://funko.blog/?status=pending',
                    'failure' => 'https://funko.blog/?status=failure',
                ],
                'auto_return' => 'approved',
                
                // Vinculacion del identificador local con la transaccion externa
                'external_reference' => strval($order->id),
                'notification_url' => 'https://funko.blog/api/payment/webhook',
            ];

            // Peticion HTTP POST hacia la API de MercadoPago
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/checkout/preferences", $preferenceData);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Error durante la creacion de la preferencia de pago',
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
                'error' => 'Fallo critico en la inicializacion del pago',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle MercadoPago webhook notifications
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Deteccion de identificadores a traves de parametros URL o cuerpo JSON
            $type = $request->query('type') ?? $request->input('type');
            $id = $request->query('id') ?? $request->input('data.id');

            if ($type === 'payment' && $id) {
                // Verificacion del estado definitivo del pago ante el proveedor
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    
                    $status = $payment['status'];
                    $orderId = $payment['external_reference'];

                    Log::info("Notificacion Webhook procesada. ID Orden: {$orderId}, Estado: {$status}");

                    // Sincronizacion del estado de la orden en la base de datos local
                    $order = Order::find($orderId);
                    if ($order) {
                        if ($status === 'approved') {
                            $order->update(['status' => 'aprobado', 'mp_payment_id' => $id]);
                            Log::info("Estado de Orden #{$orderId} actualizado a: APROBADA.");
                        } elseif ($status === 'rejected') {
                            $order->update(['status' => 'rechazado', 'mp_payment_id' => $id]);
                            Log::info("Estado de Orden #{$orderId} actualizado a: RECHAZADA.");
                        }
                    }
                }
            }

            // Confirmacion de recepcion al servidor emisor
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Excepcion en el procesamiento del Webhook:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fallo en la ejecucion del Webhook'], 500);
        }
    }

    /**
     * Callback de confirmacion de pago exitoso
     */
    public function success(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Transaccion procesada exitosamente',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Callback de pago pendiente
     */
    public function pending(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'La transaccion se encuentra pendiente de aprobacion',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Callback de pago rechazado
     */
    public function failure(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'La transaccion ha sido declinada',
            'payment_id' => $request->query('payment_id'),
        ]);
    }

    /**
     * Obtencion de clave publica para el frontend
     */
    public function getPublicKey()
    {
        return response()->json([
            'public_key' => $this->publicKey,
        ]);
    }
}