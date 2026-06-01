<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('products')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories|max:255',
            'slug' => 'required|string|unique:categories|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB máximo
        ], [
            'image.image' => 'El archivo debe ser una imagen válida',
            'image.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG, GIF o WebP',
            'image.max' => 'La imagen no debe exceder 2MB de tamaño',
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'El nombre de la categoría ya existe',
            'slug.required' => 'El slug es obligatorio',
            'slug.unique' => 'El slug ya existe',
        ]);

        $data = $request->except('image');

        // Procesar imagen si existe
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'categories/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('categories', $file, basename($filename));
            $data['image'] = $path;
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $category->load('products')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'name' => 'string|unique:categories,name,' . $id . '|max:255',
            'slug' => 'string|unique:categories,slug,' . $id . '|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'image.image' => 'El archivo debe ser una imagen válida',
            'image.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG, GIF o WebP',
            'image.max' => 'La imagen no debe exceder 2MB de tamaño',
            'name.unique' => 'El nombre de la categoría ya existe',
            'slug.unique' => 'El slug ya existe',
        ]);

        $data = $request->except('image');

        // Procesar imagen si existe
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $file = $request->file('image');
            $filename = 'categories/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('categories', $file, basename($filename));
            $data['image'] = $path;
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category->load('products')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        // Eliminar imagen si existe
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
