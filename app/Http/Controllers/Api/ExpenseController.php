<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);
        if ($request->search) $query->where('reference', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
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
            'category'        => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
            'status'          => 'in:paid,pending,overdue',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(Expense::create($data), 201);
    }

    public function show(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        return response()->json($expense->load(['contact', 'bankAccount']));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        $data = $request->validate([
            'contact_id'      => 'nullable|exists:contacts,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'reference'       => 'nullable|string|max:100',
            'date'            => 'sometimes|required|date',
            'amount'          => 'sometimes|required|numeric|min:0',
            'category'        => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
            'status'          => 'in:paid,pending,overdue',
        ]);
        $expense->update($data);
        return response()->json($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        $expense->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
