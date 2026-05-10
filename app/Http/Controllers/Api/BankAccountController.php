<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BankAccountRequest;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(BankAccount::where('user_id', $request->user()->id)->get());
    }

    public function store(BankAccountRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['current_balance'] = $data['opening_balance'] ?? 0;
        return response()->json(BankAccount::create($data), 201);
    }

    public function show(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        return response()->json($bankAccount);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        $data = $request->safe()->except('opening_balance');
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
