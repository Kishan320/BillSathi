<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(BankAccount::where('user_id', $request->user()->id)->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'in:bank,cash,credit_card,other',
            'account_number'  => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['current_balance'] = $data['opening_balance'] ?? 0;
        return response()->json(BankAccount::create($data), 201);
    }

    public function show(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        return response()->json($bankAccount);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        $data = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'type'           => 'in:bank,cash,credit_card,other',
            'account_number' => 'nullable|string|max:50',
        ]);
        $bankAccount->update($data);
        return response()->json($bankAccount);
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        $bankAccount->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
