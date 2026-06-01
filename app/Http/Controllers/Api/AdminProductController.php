<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB máximo
            'active' => 'boolean',
        ], [
            'image.image' => 'El archivo debe ser una imagen válida',
            'image.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG, GIF o WebP',
            'image.max' => 'La imagen no debe exceder 2MB de tamaño',
            'name.required' => 'El nombre del producto es obligatorio',
            'slug.required' => 'El slug del producto es obligatorio',
            'slug.unique' => 'El slug ya existe, por favor usa uno diferente',
            'price.required' => 'El precio es obligatorio',
            'price.numeric' => 'El precio debe ser un número válido',
            'stock.required' => 'El stock es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
            'category_id.required' => 'La categoría es obligatoria',
            'category_id.exists' => 'La categoría seleccionada no existe',
        ]);

        $data = $request->except('image');

        // Procesar imagen si existe
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'products/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('products', $file, basename($filename));
            $data['image'] = $path;
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $product->load('category')
        ], 201);
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
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|unique:products,slug,' . $id . '|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'integer|min:0',
            'category_id' => 'exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'active' => 'boolean',
        ], [
            'image.image' => 'El archivo debe ser una imagen válida',
            'image.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG, GIF o WebP',
            'image.max' => 'La imagen no debe exceder 2MB de tamaño',
            'slug.unique' => 'El slug ya existe, por favor usa uno diferente',
            'price.numeric' => 'El precio debe ser un número válido',
            'stock.integer' => 'El stock debe ser un número entero',
            'category_id.exists' => 'La categoría seleccionada no existe',
        ]);

        $data = $request->except('image');

        // Procesar imagen si existe
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $filename = 'products/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('products', $file, basename($filename));
            $data['image'] = $path;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $product->load('category')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        // Eliminar imagen si existe
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}
