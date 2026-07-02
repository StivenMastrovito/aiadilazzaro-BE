<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'before' => 'sometimes|nullable|date',
            'after' => 'sometimes|nullable|date',
        ]);

        $stats = OrderProduct::select('product_id', DB::raw('sum(qty) as total'))
            ->with('product.category')
            ->when($data['before'] ?? null, function ($query, $before) {
                $query->whereHas('order', function ($q) use ($before) {
                    $q->whereDate('created_at', '>=', $before);
                });
            })
            ->when($data['after'] ?? null, function ($query, $after) {
                $query->whereHas('order', function ($q) use ($after) {
                    $q->whereDate('created_at', '<=', $after);
                });
            })
            ->groupBy('product_id')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json($stats);
    }
}
