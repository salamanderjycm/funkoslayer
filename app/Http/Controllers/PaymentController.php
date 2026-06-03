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
        // Inicialización de credenciales de Mercado Pago
        $this->publicKey = config('services.mercadopago.public_key');
        $this->accessToken = config('services.mercadopago.access_token');
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Retorna la llave pública para inicializar el Brick en Vue
     */
    public function getPublicKey()
    {
        return response()->json([
            'public_key' => $this->publicKey,
        ]);
    }

    /**
     * Procesa un pago directo usando Checkout API (v1/payments)
     */
    public function processPayment(Request $request)
    {
        try {
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

            // Generación del registro persistente de la orden
            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'total' => floatval($validated['transaction_amount']),
                'status' => 'pendiente',
            ]);

            // Estructuración del payload requerido por Mercado Pago
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
            ];

            // Inyección de datos de identificación (DNI) obligatorios en Perú
            if (!empty($validated['payer']['identification']['type']) && !empty($validated['payer']['identification']['number'])) {
                $paymentData['payer']['identification'] = [
                    'type' => $validated['payer']['identification']['type'],
                    'number' => $validated['payer']['identification']['number']
                ];
            }

            // Petición HTTP POST hacia la API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => uniqid(), 
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

            // Actualización del estado de la orden local
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

            // Retorno del resultado a Vue
            return response()->json([
                'success' => true,
                'payment_id' => $payment['id'] ?? null,
                'status' => $payment['status'] ?? 'pending',
                'status_detail' => $payment['status_detail'] ?? '',
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción crítica en processPayment', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Fallo crítico en la ejecución del pago',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}