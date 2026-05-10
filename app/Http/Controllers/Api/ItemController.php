<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::where('user_id', $request->user()->id);
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        if ($request->category) $query->where('category', $request->category);
        if ($request->item_type) $query->where('item_type', $request->item_type);
        return response()->json($query->orderBy('name')->paginate(20));
    }

    public function store(ItemRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(Str::random(8));
        }

        return response()->json(Item::create($data), 201);
    }

    public function show(Request $request, Item $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        return response()->json($item);
    }

    public function update(ItemRequest $request, Item $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $data = $request->validated();
        $item->update($data);
        return response()->json($item);
    }

    public function destroy(Request $request, Item $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function generateSku()
    {
        return response()->json(['sku' => strtoupper(Str::random(8))]);
    }

    public function import(Request $request)
    {
        $request->validate(['items' => 'required|array|min:1']);

        $userId = $request->user()->id;
        $created = 0;

        foreach ($request->items as $row) {
            if (empty($row['name'])) continue;

            $data = [
                'user_id'            => $userId,
                'item_type'          => $row['item_type'] ?? 'product',
                'name'               => $row['name'],
                'hsn'                => $row['hsn'] ?? null,
                'sku'                => !empty($row['sku']) ? $row['sku'] : strtoupper(Str::random(8)),
                'category'           => $row['category'] ?? null,
                'unit'               => $row['unit'] ?? 'Pcs',
                'tax_category'       => $row['tax_category'] ?? null,
                'stock_category'     => $row['stock_category'] ?? null,
                'short_description'  => $row['short_description'] ?? null,
                'invoice_description'=> $row['invoice_description'] ?? null,
                'sale_price'         => $row['sale_price'] ?? 0,
                'sale_price_type'    => $row['sale_price_type'] ?? 'with_tax',
                'sale_discount'      => $row['sale_discount'] ?? 0,
                'sale_discount_type' => $row['sale_discount_type'] ?? 'percent',
                'purchase_price'     => $row['purchase_price'] ?? 0,
                'manage_inventory'   => $row['manage_inventory'] ?? 1,
                'opening_stock_qty'  => $row['opening_stock_qty'] ?? 0,
                'opening_stock_cost' => $row['opening_stock_cost'] ?? 0,
            ];

            Item::create($data);
            $created++;
        }

        return response()->json(['imported' => $created]);
    }
}
