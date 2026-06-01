<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::where('active', true)
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Nota: Este endpoint está disponible pero normalmente solo admins crean productos.
     * Si deseas que los clientes creen productos, puedes descomentar la lógica de abajo.
     */
    public function store(Request $request)
    {
        // Los clientes no pueden crear productos, solo ver los existentes
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para crear productos. Contacta con un administrador.'
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Los clientes no pueden actualizar productos
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para actualizar productos.'
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Los clientes no pueden eliminar productos
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para eliminar productos.'
        ], Response::HTTP_FORBIDDEN);
    }
}