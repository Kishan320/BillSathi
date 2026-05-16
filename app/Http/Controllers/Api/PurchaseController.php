<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PurchaseRequest;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['contact', 'items'])->where('user_id', $request->user()->id);
        if ($request->status) $query->where('status', $request->status);
        
        // Handle both DataTable search and regular search
        $search = $request->search ?? $request->input('search.value');
        if ($search && is_string($search)) {
            $search = trim($search);
            $query->where('invoice_number', 'like', '%' . addslashes($search) . '%');
        }
        
        // Handle DataTable sorting
        if ($request->has('order')) {
            $orderColumn = $request->input('order.0.column', 2);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['invoice_number', 'contact', 'date', 'due_date', 'subtotal', 'tax', 'total', 'status'];
            $sortBy = $columns[$orderColumn] ?? 'date';
            if ($sortBy === 'contact') {
                $query->orderBy('date', $orderDir); // contact is a relation, fallback to date
            } else {
                $query->orderBy($sortBy, $orderDir);
            }
        } else {
            $query->orderByDesc('date');
        }
        
        // Handle DataTable pagination
        $perPage = $request->length ?? $request->per_page ?? 10;
        $page = ($request->start ?? 0) / $perPage + 1;
        
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Return DataTable format if draw parameter exists
        if ($request->has('draw')) {
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $paginated->total(),
                'recordsFiltered' => $paginated->total(),
                'data' => $paginated->items(),
            ]);
        }
        
        return response()->json($paginated);
    }

    public function store(PurchaseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['invoice_number'] = '#' . strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 12));

        $purchase = Purchase::create($data);
        $this->syncItems($purchase, $items);
        $this->recalculate($purchase);

        return response()->json($purchase->load('items'), 201);
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        return response()->json($purchase->load(['contact', 'bankAccount', 'items.item']));
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $data = $request->validated();
        $items = $data['items'] ?? null;
        unset($data['items']);

        $purchase->update($data);
        if ($items !== null) {
            $this->syncItems($purchase, $items);
            $this->recalculate($purchase);
        }

        return response()->json($purchase->load('items'));
    }

    public function destroy(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $purchase->delete();
        return response()->json(['message' => 'Deleted']);
    }

    private function syncItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->delete();
        foreach ($items as $item) {
            $purchase->items()->create([
                'item_id'    => $item['item_id'] ?? null,
                'item_name'  => $item['item_name'],
                'qty'        => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount'   => $item['discount'] ?? 0,
                'taxes'      => $item['taxes'] ?? [],
                'total'      => $item['total'],
            ]);
        }
    }

    private function recalculate(Purchase $purchase): void
    {
        $purchase->refresh();
        $subtotal = $purchase->items->sum(fn($i) => $i->qty * $i->unit_price - $i->discount);
        $tax = $purchase->items->sum(function ($i) {
            $base = $i->qty * $i->unit_price - $i->discount;
            $taxRate = collect($i->taxes ?? [])->sum(fn($t) => (float) filter_var($t, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            return $base * $taxRate / 100;
        });
        $purchase->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax]);
    }
}
