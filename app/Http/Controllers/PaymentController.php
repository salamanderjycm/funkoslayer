<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class PaymentController extends Controller
{
    private $accessToken;
    private $apiUrl;

    public function __construct()
    {
        // Para Checkout Pro, solo necesitamos el Access Token
        $this->accessToken = config('services.mercadopago.access_token');
        $this->apiUrl = 'https://api.mercadopago.com';
    }

    /**
     * Crea una "Preferencia" de Checkout Pro y devuelve el link de pago
     */
    public function createPreference(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'quantity' => 'required|integer',
                'unit_price' => 'required|numeric',
            ]);

            // Creamos la orden en tu base de datos
            $order = Order::create([
                'user_id' => auth()->id() ?? 1,
                'total' => $validated['quantity'] * $validated['unit_price'],
                'status' => 'pendiente',
            ]);

            // Armamos la orden para Mercado Pago
            $preferenceData = [
                'items' => [
                    [
                        'title' => $validated['title'],
                        'quantity' => $validated['quantity'],
                        'currency_id' => 'PEN',
                        'unit_price' => floatval($validated['unit_price'])
                    ]
                ],
                'back_urls' => [
                    // A donde regresará el usuario luego de pagar (Ajusta estas URLs según tu frontend)
                    'success' => 'https://funko.blog/cart?status=success',
                    'failure' => 'https://funko.blog/cart?status=failure',
                    'pending' => 'https://funko.blog/cart?status=pending'
                ],
                'auto_return' => 'approved',
                'external_reference' => strval($order->id),
            ];

            // Petición a la API de Mercado Pago
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/checkout/preferences", $preferenceData);

            if ($response->failed()) {
                Log::error('Error creando preferencia MP:', $response->json());
                return response()->json(['error' => 'Fallo al conectar con Mercado Pago'], 500);
            }

            $data = $response->json();

            return response()->json([
                'success' => true,
                'init_point' => $data['sandbox_init_point'] // Link para entorno de pruebas
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción en createPreference', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}