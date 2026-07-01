<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class productsController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $products->load('category');

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0.50',
            'description' => 'sometimes',
            'category_id' => 'required|numeric|exists:categories,id'
        ]);

        $product = new Product();
        $product->name = $data['name'];
        $product->price = $data['price'];
        if (!empty($data['description'])) {
            $product->description = $data['description'];
        }
        $product->category_id = $data['category_id'];
        $product->save();

        $products = Product::all();
        $products->load('category');

        return response()->json([
            'message' => 'Prodotto aggiunto con successo!',
            'data' => $products,
        ]);
    }

    public function show(Product $product)
    {
        $product->load('category');
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|numeric|nullable|min:0.50',
            'category_id' => 'sometimes|nullable|exists:categories,id'
        ]);

        $product->update(array_filter($data, fn($value) => !is_null($value)));

        $products = Product::all();
        $products->load('category');

        return response()->json([
            'message' => 'Prodotto aggiornato con successo!',
            'data' => $products
        ]);
    }
    public function destroy(Product $product)
    {
        $product->delete();
        $products = Product::all();
        $products->load('category');
        return response()->json([
            'message' => 'Prodotto eliminato con successo!',
            'data' => $products
        ]);
    }
}
