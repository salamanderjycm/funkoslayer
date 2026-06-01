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
        $this->publicKey = config('services.mercadopago.public_key');
        $this->accessToken = config('services.mercadopago.access_token');
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Procesa un pago directo usando Checkout API (v1/payments)
     */
    public function processPayment(Request $request)
    {
        try {
            // Validacion de la estructura de datos entrante desde el cliente (Frontend)
            // En Checkout API, el frontend debe enviar el token de la tarjeta y el monto exacto
            $validated = $request->validate([
                'transaction_amount' => 'required|numeric',
                'token' => 'required|string',
                'description' => 'required|string',
                'installments' => 'required|integer',
                'payment_method_id' => 'required|string',
                'payer' => 'required|array',
                'payer.email' => 'required|email',
                'payer.identification.type' => 'nullable|string',
                'payer.identification.number' => 'nullable|string',
            ]);

            // Generacion del registro persistente de la orden
            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'total' => floatval($validated['transaction_amount']),
                'status' => 'pendiente',
            ]);

            // Estructuracion del payload requerido por la API de Pagos (V1)
            $paymentData = [
                'transaction_amount' => floatval($validated['transaction_amount']),
                'token' => $validated['token'],
                'description' => $validated['description'],
                'installments' => intval($validated['installments']),
                'payment_method_id' => $validated['payment_method_id'],
                'payer' => [
                    'email' => $validated['payer']['email'],
                ],
                'external_reference' => strval($order->id),
                'notification_url' => 'https://funko.blog/api/payment/webhook',
            ];

            // Inyeccion de datos de identificacion si estan presentes
            if (!empty($validated['payer']['identification']['type']) && !empty($validated['payer']['identification']['number'])) {
                $paymentData['payer']['identification'] = [
                    'type' => $validated['payer']['identification']['type'],
                    'number' => $validated['payer']['identification']['number']
                ];
            }

            // Peticion HTTP POST hacia la API de MercadoPago v1/payments
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => uniqid(), // Clave unica para evitar cobros duplicados
            ])->post("{$this->apiUrl}/v1/payments", $paymentData);

            $payment = $response->json();

            // Manejo de errores de la pasarela
            if ($response->failed()) {
                Log::error('ERROR MERCADOPAGO (Checkout API)', [
                    'status' => $response->status(),
                    'json' => $payment,
                ]);

                return response()->json([
                    'error' => 'Error al procesar el pago con la tarjeta',
                    'details' => $payment
                ], 422);
            }

            // Actualizacion del estado de la orden local basado en la respuesta sincrona
            if (isset($payment['status'])) {
                $newStatus = match ($payment['status']) {
                    'approved' => 'aprobado',
                    'rejected' => 'rechazado',
                    default => 'pendiente',
                };
                
                $order->update([
                    'status' => $newStatus,
                    'mp_payment_id' => $payment['id'] ?? null
                ]);
            }

            // Retorno del resultado al cliente
            return response()->json([
                'success' => true,
                'payment_id' => $payment['id'] ?? null,
                'status' => $payment['status'] ?? 'pending',
                'status_detail' => $payment['status_detail'] ?? '',
            ]);

        } catch (\Exception $e) {
            Log::error('Excepcion critica en processPayment', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Fallo critico en la ejecucion del pago',
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
            $type = $request->query('type') ?? $request->input('type');
            $id = $request->query('id') ?? $request->input('data.id');

            if ($type === 'payment' && $id) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    $status = $payment['status'];
                    $orderId = $payment['external_reference'];

                    Log::info("Notificacion Webhook procesada. ID Orden: {$orderId}, Estado: {$status}");

                    $order = Order::find($orderId);
                    if ($order) {
                        if ($status === 'approved') {
                            $order->update(['status' => 'aprobado', 'mp_payment_id' => $id]);
                        } elseif ($status === 'rejected') {
                            $order->update(['status' => 'rechazado', 'mp_payment_id' => $id]);
                        }
                    }
                }
            }
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Excepcion en el procesamiento del Webhook:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Fallo en la ejecucion del Webhook'], 500);
        }
    }

    public function getPublicKey()
    {
        return response()->json([
            'public_key' => $this->publicKey,
        ]);
    }
}