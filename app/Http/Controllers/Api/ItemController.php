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
}
