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
        $query = Purchase::with(['contact'])->where('user_id', $request->user()->id);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    public function store(PurchaseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Purchase::create($data), 201);
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        return response()->json($purchase->load(['contact', 'bankAccount']));
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $purchase->update($request->validated());
        return response()->json($purchase);
    }

    public function destroy(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $purchase->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
