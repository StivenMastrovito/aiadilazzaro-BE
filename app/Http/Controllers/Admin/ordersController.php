<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class ordersController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->limit(20)->get();
        $orders->load('products', 'table');
        return response()->json($orders);
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string',
            'table_id' => 'required|numeric|exists:tables,id',
            'peoples' => 'required|numeric|min:1'
        ]);


        $price = $data['peoples'] * 2;


        $ultimoOrdine = Order::latest('number_order')->first();
        $prossimoOrdine = 1;

        $todayThree = Carbon::today()->setTime(3, 0, 0);

        if ($ultimoOrdine) {
            $createdAt = Carbon::parse($ultimoOrdine->created_at);

            if ($createdAt->greaterThanOrEqualTo($todayThree)) {
                $prossimoOrdine = $ultimoOrdine->number_order + 1;
            }
        }

        $order = new Order();
        $order->total_price = $price;
        $order->table_id = $data['table_id'];
        $order->number_order = $prossimoOrdine;
        $order->name = $data['name'];
        $order->peoples = $data['peoples'];
        $order->save();

        $table = Table::find($data['table_id']);
        $table->name = $data['name'];
        $table->open_order_id = $order->id;
        $table->save();

        $tables = Table::all();

        $order->load('table', 'products');

        return response()->json([
            'message' => 'Ordine salvato con successo!',
            'data' => $order,
            'tables' => $tables,
        ]);
    }

    public function show(Order $order)
    {
        $order->load('products', 'table');
        return response()->json($order);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'table_id' => 'sometimes|nullable|numeric|exists:tables,id',
            'products' => 'sometimes|array',
            'peoples' => 'sometimes|numeric|min:1',
        ]);


        $order->update(array_filter([
            'name' => $data['name'] ?? null,
            'table_id' => $data['table_id'] ?? null,
            'peoples' => $data['peoples'] ?? null,
        ], fn($value) => !is_null($value)));


        $order->load('table', 'products');

        if (isset($data['products'])) {
            $formattedProducts = collect($data['products'])->map(function ($product) {
                return [
                    'id'   => $product['id'],
                    'qty'  => $product['qty'],
                    'scope'  => $product['scope'],
                    'note' => $product['note'] ?? null,
                ];
            })->all();

            foreach ($formattedProducts as $pivot) {
                $productId = $pivot['id'];

                if (!empty($pivot['note'])) {
                    $order->products()->attach($productId, [
                        'qty'  => $pivot['qty'],
                        'note' => $pivot['note'],
                        'scope' => $pivot['scope'],
                    ]);
                    // continue;
                } else {
                    $order->products()->attach($productId, [
                        'qty'  => $pivot['qty'],
                        'scope' => $pivot['scope'],
                        'note' => null,
                    ]);
                }
            }
        }

        $order->load('products');

        $total_price = $order->peoples * 2;
        foreach ($order->products as $product) {
            $total_price += $product->price * $product->pivot->qty;
        }
        $order->total_price = $total_price;
        $order->save();


        if (isset($data['name'])) {
            $table = Table::find($data['table_id']);
            $table->name = $data['name'];
            $table->save();
        }

        $order->load('products', 'table');

        $tables = Table::all();

        return response()->json([
            'message' => 'Ordine aggiornato con successo!',
            'data' => $order,
            'tables' => $tables,
        ]);
    }

    public function destroy(Order $order)
    {
        $table = Table::find($order->table_id);

        $table->open_order_id = null;
        $table->name = null;
        $table->save();

        $order->products()->detach();
        $order->delete();

        return response()->json([
            'message' => 'Ordine chiuso con successo!',
        ]);
    }

    public function removeProducts(Request $request, Order $order)
    {
        $data = $request->validate([
            'products' => 'required|array',
        ]);

        $order->load('products', 'table');
        foreach ($data['products'] as $product) {
            $newProduct = $order->products()->wherePivot('id', $product['pivot']['id'])->first();
            if ($newProduct['pivot']['qty'] <= $product['qty']) {
                OrderProduct::where('id', $product['pivot']['id'])->delete();
            } else {
                OrderProduct::where('id', $product['pivot']['id'])->update([
                    'qty' => $newProduct['pivot']['qty'] - $product['qty'],
                ]);
            }
        }
        $order->load('products', 'table');

        $total_price = $order->peoples * 2;
        foreach ($order->products as $prod) {
            $total_price += $prod->price * $prod->pivot->qty;
        }
        $order->total_price = $total_price;
        $order->save();

        $order->load('products', 'table');

        return response()->json([
            'message' => 'Prodotto rimosso con successo!',
            'data' => $order
        ]);
    }

    public function removeProductsWithNote(Request $request, Order $order)
    {
        $data = $request->validate([
            'products' => 'required|array',
        ]);

        foreach ($data['products'] as $product) {
            OrderProduct::where('id', $product['pivot']['id'])->delete();
        };

        $order->load('products', 'table');

        $total_price = $order->peoples * 2;
        foreach ($order->products as $prod) {
            $total_price += $prod->price * $prod->pivot->qty;
        }
        $order->total_price = $total_price;
        $order->save();

        $order->load('products', 'table');

        return response()->json([
            'message' => 'Prodotto rimosso con successo!',
            'data' => $order
        ]);
    }

    public function closeOrder(Order $order)
    {
        $table = Table::where('open_order_id', $order->id)->first();

        $table->open_order_id = 0;
        $table->name = null;
        $table->save();

        $tables = Table::all();

        return response()->json([
            'message' => 'Ordine chiuso con successo!',
            'data' => $tables,
        ]);
    }

    public function getUnprinted(Order $order)
    {
        $products = OrderProduct::where('printed', false)->where('order_id', $order->id)->with('product')->get();
        $order->load('table', 'products');
        return response()->json([
            'products' => $products,
            'order' => $order,
        ]);
    }

    public function updatePrinted(Request $request)
    {
        $data = $request->validate([
            'array_id' => 'required|array',
            'array_id.*' => 'integer|exists:order_product,id',
        ]);

        OrderProduct::whereIn('id', $data['array_id'])
            ->update(['printed' => true]);

        return response()->json([
            'message' => 'Prodotti aggiornati correttamente'
        ]);
    }
}
