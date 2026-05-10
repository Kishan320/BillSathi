<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'invoice_number'  => 'nullable|string|max:100',
            'date'            => 'required|date',
            'due_date'        => 'nullable|date',
            'subtotal'        => 'nullable|numeric|min:0',
            'tax'             => 'nullable|numeric|min:0',
            'total'           => 'required|numeric|min:0',
            'paid'            => 'nullable|numeric|min:0',
            'status'          => 'in:draft,pending,paid,overdue,cancelled',
            'notes'           => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(Purchase::create($data), 201);
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        return response()->json($purchase->load(['contact', 'bankAccount']));
    }

    public function update(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $purchase->update($request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'invoice_number'  => 'nullable|string|max:100',
            'date'            => 'sometimes|required|date',
            'due_date'        => 'nullable|date',
            'subtotal'        => 'nullable|numeric|min:0',
            'tax'             => 'nullable|numeric|min:0',
            'total'           => 'sometimes|required|numeric|min:0',
            'paid'            => 'nullable|numeric|min:0',
            'status'          => 'in:draft,pending,paid,overdue,cancelled',
            'notes'           => 'nullable|string',
        ]));
        return response()->json($purchase);
    }

    public function destroy(Request $request, Purchase $purchase)
    {
        abort_if($purchase->user_id !== $request->user()->id, 403);
        $purchase->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
