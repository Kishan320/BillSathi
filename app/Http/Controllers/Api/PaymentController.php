<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);
        if ($request->type) $query->where('type', $request->type);
        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'reference'       => 'nullable|string|max:100',
            'date'            => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'type'            => 'in:sent,received',
            'method'          => 'nullable|string|max:50',
            'notes'           => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(Payment::create($data), 201);
    }

    public function show(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        return response()->json($payment->load(['contact', 'bankAccount']));
    }

    public function update(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'reference'       => 'nullable|string|max:100',
            'date'            => 'sometimes|required|date',
            'amount'          => 'sometimes|required|numeric|min:0',
            'type'            => 'in:sent,received',
            'method'          => 'nullable|string|max:50',
            'notes'           => 'nullable|string',
        ]);
        $payment->update($data);
        return response()->json($payment);
    }

    public function destroy(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $payment->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
