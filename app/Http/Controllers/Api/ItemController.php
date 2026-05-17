<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::query()->where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json($query->orderBy('name')->paginate($perPage));
    }

    public function datatable(Request $request)
    {
        $query = Item::where('user_id', $request->user()->id)
            ->select(['id', 'name', 'item_type', 'sku', 'category', 'unit', 'sale_price', 'opening_stock_qty', 'short_description']);

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        return DataTables::of($query)->make(true);
    }

    public function store(ItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        $userId = $request->user()->id;
        $data['user_id'] = $userId;

        if (empty($data['sku'])) {
            do {
                $sku = strtoupper(Str::random(8));
            } while (Item::query()->where('user_id', $userId)->where('sku', $sku)->exists());
            $data['sku'] = $sku;
        }

        return response()->json(Item::create($data), 201);
    }

    public function show(Request $request, Item $item): JsonResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        return response()->json($item);
    }

    public function update(ItemRequest $request, Item $item): JsonResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $data = $request->validated();

        if (array_key_exists('sku', $data) && ($data['sku'] === null || $data['sku'] === '')) {
            unset($data['sku']);
        }

        $item->update($data);
        return response()->json($item);
    }

    public function destroy(Request $request, Item $item): JsonResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function generateSku(): JsonResponse
    {
        $userId = request()->user()?->id;
        if (!$userId) {
            abort(401);
        }

        do {
            $sku = strtoupper(Str::random(8));
        } while (Item::query()->where('user_id', $userId)->where('sku', $sku)->exists());

        return response()->json(['sku' => $sku]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;
        $now = now();

        $rows = [];
        foreach ((array) $request->items as $row) {
            $itemType = $row['item_type'] ?? 'product';
            if (!in_array($itemType, ['product', 'service', 'charge'], true)) {
                $itemType = 'product';
            }

            $sku = isset($row['sku']) && $row['sku'] !== '' ? (string) $row['sku'] : strtoupper(Str::random(8));

            $manageInventory = (int) ($row['manage_inventory'] ?? 1);
            $serializedProduct = (int) ($row['serialized_product'] ?? 0);

            if ($itemType !== 'product') {
                $manageInventory = 0;
                $serializedProduct = 0;
            }

            $rowData = [
                'user_id' => $userId,
                'item_type' => $itemType,
                'name' => (string) $row['name'],
                'hsn' => $row['hsn'] ?? null,
                'sku' => $sku,
                'category' => $row['category'] ?? null,
                'unit' => $row['unit'] ?? 'Pcs',
                'tax_category' => $row['tax_category'] ?? null,
                'stock_category' => $row['stock_category'] ?? null,
                'short_description' => $row['short_description'] ?? null,
                'invoice_description' => $row['invoice_description'] ?? null,
                'sale_price' => (float) ($row['sale_price'] ?? 0),
                'sale_price_type' => $row['sale_price_type'] ?? 'with_tax',
                'sale_discount' => (float) ($row['sale_discount'] ?? 0),
                'sale_discount_type' => $row['sale_discount_type'] ?? 'percent',
                'purchase_price' => (float) ($row['purchase_price'] ?? 0),
                'manage_inventory' => $manageInventory ? 1 : 0,
                'serialized_product' => $serializedProduct ? 1 : 0,
                'opening_stock_qty' => (float) ($row['opening_stock_qty'] ?? 0),
                'opening_stock_cost' => (float) ($row['opening_stock_cost'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($itemType === 'charge') {
                $rowData['unit'] = $rowData['unit'] ?: 'Pcs';
                $rowData['tax_category'] = null;
                $rowData['invoice_description'] = null;
            }

            $rows[] = $rowData;
        }

        if (empty($rows)) {
            return response()->json(['imported' => 0]);
        }

        DB::transaction(function () use ($rows) {
            Item::insert($rows);
        });

        return response()->json(['imported' => count($rows)]);
    }
}
