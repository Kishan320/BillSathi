<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExpenseRequest;
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

    public function store(ExpenseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Expense::create($data), 201);
    }

    public function show(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        return response()->json($expense->load(['contact', 'bankAccount']));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        $data = $request->validated();
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
