<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class categoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string'
        ]);

        $category = new Category();
        $category->name = $data['name'];
        $category->save();
        $categories = Category::all();

        return response()->json([
            'message' => 'Categoria creata con successo!',
            'data' => $categories,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $category->name = $data['name'];
        $category->save();

        $categories = Category::all();

        return response()->json([
            'message' => 'Categoria aggiornata con successo!',
            'data' => $categories,
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        $categories = Category::all();
        return response()->json([
            'message' => 'Categoria eliminata con successo!',
            'data' => $categories,
        ]);
    }
}
