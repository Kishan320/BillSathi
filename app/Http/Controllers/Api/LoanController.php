<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Loan::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id)
                ->orderByDesc('date')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'type'            => 'in:given,taken',
            'amount'          => 'required|numeric|min:0',
            'interest_rate'   => 'nullable|numeric|min:0',
            'date'            => 'required|date',
            'due_date'        => 'nullable|date',
            'status'          => 'in:active,closed,overdue',
            'notes'           => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(Loan::create($data), 201);
    }

    public function show(Request $request, Loan $loan)
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        return response()->json($loan->load(['contact', 'bankAccount']));
    }

    public function update(Request $request, Loan $loan)
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        $loan->update($request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'type'            => 'in:given,taken',
            'amount'          => 'sometimes|required|numeric|min:0',
            'interest_rate'   => 'nullable|numeric|min:0',
            'date'            => 'sometimes|required|date',
            'due_date'        => 'nullable|date',
            'status'          => 'in:active,closed,overdue',
            'notes'           => 'nullable|string',
        ]));
        return response()->json($loan);
    }

    public function destroy(Request $request, Loan $loan)
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        $loan->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
