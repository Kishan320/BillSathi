<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Settlement::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id)
                ->orderByDesc('date')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'date'            => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(Settlement::create($data), 201);
    }

    public function show(Request $request, Settlement $settlement)
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        return response()->json($settlement->load(['contact', 'bankAccount']));
    }

    public function update(Request $request, Settlement $settlement)
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        $settlement->update($request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'date'            => 'sometimes|required|date',
            'amount'          => 'sometimes|required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]));
        return response()->json($settlement);
    }

    public function destroy(Request $request, Settlement $settlement)
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        $settlement->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
