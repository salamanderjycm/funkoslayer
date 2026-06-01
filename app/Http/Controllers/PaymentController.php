<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private $publicKey;
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        $this->publicKey = env('MERCADOPAGO_PUBLIC_KEY');
        $this->accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
        
        // 🚀 CORRECCIÓN: La API de MercadoPago ahora es única para pruebas y producción.
        // El entorno lo define tu token (TEST- para pruebas, APP_USR- para producción)
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Create a MercadoPago preference for the payment
     */
    public function createPreference(Request $request)
    {
        try {
            // Validate incoming data
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
                'external_reference' => 'required|string',
            ]);

            // Calculate totals
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Tax calculation (10%)
            $tax = $total * 0.10;
            $totalWithTax = $total + $tax;

            // Prepare preference data for MercadoPago
            // Prepare preference data for MercadoPago
            $preferenceData = [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'],
                        'quantity' => $item['quantity'],
                        'unit_price' => floatval($item['unit_price']),
                    ];
                }, $validated['items']),
                'payer' => [
                    // ... tus datos del payer se quedan igual ...
                    'name' => $validated['payer']['name'],
                    'email' => $validated['payer']['email'],
                    'phone' => [
                        'area_code' => '34',
                        'number' => str_replace(['+', ' ', '-'], '', $validated['payer']['phone']),
                    ],
                    'address' => [
                        'street_name' => $validated['payer']['address'],
                    ],
                ],
                
                // 👇 ESTE ES EL BLOQUE QUE DEBES REEMPLAZAR 👇
                'back_urls' => [
                    // request()->getSchemeAndHttpHost() obtiene automáticamente 'http://127.0.0.1:8000'
                    'success' => request()->getSchemeAndHttpHost() . '/?status=success',
                    'pending' => request()->getSchemeAndHttpHost() . '/?status=pending',
                    'failure' => request()->getSchemeAndHttpHost() . '/?status=failure',
                ],
                'auto_return' => 'approved',
                // 👆 HASTA AQUÍ 👆
                
                'external_reference' => $validated['external_reference'],
                'notification_url' => request()->getSchemeAndHttpHost() . '/api/payment/webhook',
            ];

            // Create preference via MercadoPago API
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
     * Create payment with card (Direct payment method)
     */
    public function createPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'payment_method_id' => 'required|string',
                'installments' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string',
                'payer_email' => 'required|email',
                'external_reference' => 'required|string',
            ]);

            // First, obtain card token if not provided
            $token = $validated['token'];

            // Create payment
            $paymentData = [
                'token' => $token,
                'payment_method_id' => $validated['payment_method_id'],
                'installments' => $validated['installments'],
                'amount' => floatval($validated['amount']),
                'description' => $validated['description'],
                'payer' => [
                    'email' => $validated['payer_email'],
                ],
                'external_reference' => $validated['external_reference'],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => uniqid(),
            ])->post("{$this->apiUrl}/v1/payments", $paymentData);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Payment processing failed',
                    'details' => $response->json(),
                ], 422);
            }

            $payment = $response->json();

            // Check payment status
            if ($payment['status'] === 'approved') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment approved successfully',
                    'payment_id' => $payment['id'],
                    'status' => $payment['status'],
                ]);
            } elseif ($payment['status'] === 'pending') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment pending approval',
                    'payment_id' => $payment['id'],
                    'status' => $payment['status'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment declined',
                    'payment_id' => $payment['id'],
                    'status' => $payment['status'],
                    'status_detail' => $payment['status_detail'] ?? 'Unknown',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->apiUrl}/v1/payments/{$paymentId}");

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to get payment status',
                ], 422);
            }

            $payment = $response->json();

            return response()->json([
                'payment_id' => $payment['id'],
                'status' => $payment['status'],
                'amount' => $payment['transaction_amount'],
                'date' => $payment['date_created'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch payment status',
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
            $type = $request->query('type');
            $id = $request->query('id');

            if ($type === 'payment') {
                // Fetch payment details
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    
                    // Process payment based on status
                    // You can store payment records in database here
                    \Log::info('MercadoPago payment webhook:', [
                        'payment_id' => $payment['id'],
                        'status' => $payment['status'],
                        'amount' => $payment['transaction_amount'],
                    ]);
                }
            }

            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            \Log::error('Webhook processing error:', ['error' => $e->getMessage()]);
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