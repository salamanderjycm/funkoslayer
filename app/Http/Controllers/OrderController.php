<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        // Buscamos las órdenes del usuario logueado, de la más nueva a la más antigua
        $orders = Order::where('user_id', auth()->id())
                       ->orderBy('created_at', 'desc')
                       ->get();

        // Decodificamos el 'cart_data' de cada orden para que Vue lo lea como un array real
        $orders->transform(function ($order) {
            $order->items = json_decode($order->cart_data);
            return $order;
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}