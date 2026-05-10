<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_type'            => 'in:product,service',
            'manage_inventory'     => 'nullable|in:0,1',
            'serialized_product'   => 'nullable|in:0,1',
            'name'                 => 'required|string|max:255',
            'hsn'                  => 'nullable|string|max:50',
            'sku'                  => 'nullable|string|max:100',
            'category'             => 'nullable|string|max:100',
            'unit'                 => 'nullable|string|max:50',
            'tax_category'         => 'nullable|string|max:100',
            'stock_category'       => 'nullable|string|max:100',
            'short_description'    => 'nullable|string|max:255',
            'invoice_description'  => 'nullable|string|max:4000',
            'sale_price'           => 'nullable|numeric|min:0',
            'sale_price_type'      => 'in:with_tax,without_tax',
            'sale_discount'        => 'nullable|numeric|min:0',
            'sale_discount_type'   => 'in:percent,amount',
            'purchase_price'       => 'nullable|numeric|min:0',
            'opening_stock_qty'    => 'nullable|numeric|min:0',
            'opening_stock_cost'   => 'nullable|numeric|min:0',
            'serial_numbers'       => 'nullable|string',
        ]);

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

    public function update(Request $request, Item $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $data = $request->validate([
            'item_type'            => 'in:product,service',
            'manage_inventory'     => 'nullable|in:0,1',
            'serialized_product'   => 'nullable|in:0,1',
            'name'                 => 'sometimes|required|string|max:255',
            'hsn'                  => 'nullable|string|max:50',
            'sku'                  => 'nullable|string|max:100',
            'category'             => 'nullable|string|max:100',
            'unit'                 => 'nullable|string|max:50',
            'tax_category'         => 'nullable|string|max:100',
            'stock_category'       => 'nullable|string|max:100',
            'short_description'    => 'nullable|string|max:255',
            'invoice_description'  => 'nullable|string|max:4000',
            'sale_price'           => 'nullable|numeric|min:0',
            'sale_price_type'      => 'in:with_tax,without_tax',
            'sale_discount'        => 'nullable|numeric|min:0',
            'sale_discount_type'   => 'in:percent,amount',
            'purchase_price'       => 'nullable|numeric|min:0',
            'opening_stock_qty'    => 'nullable|numeric|min:0',
            'opening_stock_cost'   => 'nullable|numeric|min:0',
            'serial_numbers'       => 'nullable|string',
        ]);
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
}
