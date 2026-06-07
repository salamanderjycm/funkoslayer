<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        // Buscamos las órdenes del usuario, PERO ignoramos las que tienen el carrito nulo
        $orders = Order::where('user_id', auth()->id())
                       ->whereNotNull('cart_data') // <-- ESTA ES LA LÍNEA MÁGICA
                       ->orderBy('created_at', 'desc')
                       ->get();

        // Decodificamos el 'cart_data' para que Vue lo lea como un array real
        $orders->transform(function ($order) {
            $order->items = json_decode($order->cart_data) ?? [];
            return $order;
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
    // Obtener TODAS las órdenes para el panel de control
    public function adminOrders(Request $request)
    {
        // Doble seguridad: Verificar que quien pide esto sea un admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }

        // Traemos todas las órdenes, incluyendo los datos del usuario que compró
        $orders = Order::with('user')
                       ->whereNotNull('cart_data')
                       ->orderBy('created_at', 'desc')
                       ->get();

        $orders->transform(function ($order) {
            $order->items = json_decode($order->cart_data) ?? [];
            return $order;
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    // Actualizar el estado de una orden (ej: de 'aprobado' a 'enviado')
    public function updateStatus(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }

        $order = Order::find($id);
        if ($order) {
            $order->status = $request->status;
            $order->save();
            
            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente']);
        }

        return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
    }
}