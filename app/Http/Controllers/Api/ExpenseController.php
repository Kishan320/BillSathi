<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExpenseRequest;
use App\Models\Expense;
use App\Services\BankLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private BankLedgerService $ledger) {}

    public function index(Request $request)
    {
        $query = Expense::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);
        if ($request->search) $query->where('reference', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    public function store(ExpenseRequest $request)
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;

        $expense = DB::transaction(function () use ($userId, $data) {
            $expense = Expense::create($data);

            if (!empty($expense->bank_account_id)) {
                $this->ledger->upsertForSource(
                    $userId,
                    'expense',
                    (int) $expense->bank_account_id,
                    'debit',
                    (float) $expense->amount,
                    (string) $expense->date,
                    $expense,
                    null,
                    $expense->reference ? "Expense: {$expense->reference}" : null
                );
            }

            return $expense;
        });

        return response()->json($expense, 201);
    }

    public function show(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        return response()->json($expense->load(['contact', 'bankAccount']));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        $data = $request->validated();

        DB::transaction(function () use ($userId, $expense, $data) {
            $expense->update($data);
            $expense->refresh();

            $this->ledger->upsertForSource(
                $userId,
                'expense',
                $expense->bank_account_id ? (int) $expense->bank_account_id : null,
                'debit',
                (float) $expense->amount,
                (string) $expense->date,
                $expense,
                null,
                $expense->reference ? "Expense: {$expense->reference}" : null
            );
        });

        return response()->json($expense->refresh());
    }

    public function destroy(Request $request, Expense $expense)
    {
        abort_if($expense->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        DB::transaction(function () use ($userId, $expense) {
            $this->ledger->upsertForSource($userId, 'expense', null, 'debit', 0, now()->toDateString(), $expense);
            $expense->delete();
        });
        return response()->json(['message' => 'Deleted']);
    }
}
