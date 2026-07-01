<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class tablesController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('number')->get();
        return response()->json($tables);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|numeric',
        ]);

        $table = new Table();
        $table->number = $data['number'];
        $table->save();
        $tables = Table::all();

        return response()->json([
            'message' => 'Tavolo creato con successo!',
            'data' => $tables,
        ]);
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'number' => 'required|numeric',
        ]);

        $table->number = $data['number'];
        $table->save();
        $tables = Table::all();

        return response()->json([
            'message' => 'Tavolo aggiornato con successo!',
            'data' => $tables,
        ]);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        $tables = Table::all();
        return response()->json([
            'message' => 'Tavolo eliminato con successo!',
            'data' => $tables,
        ]);
    }
}
