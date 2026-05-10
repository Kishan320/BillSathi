<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
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

    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Payment::create($data), 201);
    }

    public function show(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        return response()->json($payment->load(['contact', 'bankAccount']));
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $data = $request->validated();
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
