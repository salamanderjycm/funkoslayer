<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $publicKey;
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        // ✅ CORRECCIÓN: Leemos desde config() para evitar el error de caché en producción
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
                'external_reference' => 'required|string', // ID de tu Orden Local
            ]);

            $preferenceData = [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'],
                        'quantity' => intval($item['quantity']),
                        'unit_price' => floatval($item['unit_price']),
                        'currency_id' => 'PEN' // Aseguramos tu moneda local (Soles)
                    ];
                }, $validated['items']),
                'payer' => [
                    'name' => $validated['payer']['name'],
                    'email' => $validated['payer']['email'],
                    'phone' => [
                        'area_code' => '51', // Código de Perú
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
                'external_reference' => $validated['external_reference'],
                'notification_url' => 'https://funko.blog/api/payment/webhook',
            ];

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
            // ✅ SOPORTE DUO: Detecta si los datos vienen por URL o por el Body JSON
            $type = $request->query('type') ?? $request->input('type');
            $id = $request->query('id') ?? $request->input('data.id');

            if ($type === 'payment' && $id) {
                // Consultar los detalles oficiales del pago directamente a MercadoPago
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ])->get("{$this->apiUrl}/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    
                    $status = $payment['status']; // approved, pending, rejected
                    $orderId = $payment['external_reference']; // El ID de tu orden local

                    Log::info("MercadoPago Webhook recibido. Orden: {$orderId}, Estado: {$status}");
                }
            }

            // OBLIGATORIO: Responderle 200/201 a MercadoPago para que no repita la alerta
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    // Los métodos success, pending, failure y getPublicKey se quedan igual...
}