<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IncomeRequest;
use App\Models\Income;
use App\Services\BankLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(private BankLedgerService $ledger)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Income::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where('reference', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json($query->orderByDesc('date')->paginate($perPage));
    }

    public function store(IncomeRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;

        $income = DB::transaction(function () use ($userId, $data) {
            $income = Income::create($data);

            if (!empty($income->bank_account_id)) {
                $this->ledger->upsertForSource(
                    $userId,
                    'deposit',
                    (int) $income->bank_account_id,
                    'credit',
                    (float) $income->amount,
                    (string) $income->date,
                    $income,
                    null,
                    $income->reference ? "Income: {$income->reference}" : null
                );
            }

            return $income->refresh();
        });

        return response()->json($income, 201);
    }

    public function show(Request $request, Income $income): JsonResponse
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        return response()->json($income->load(['contact', 'bankAccount']));
    }

    public function update(IncomeRequest $request, Income $income): JsonResponse
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        $data = $request->validated();

        DB::transaction(function () use ($userId, $income, $data) {
            $income->update($data);
            $income->refresh();

            $this->ledger->upsertForSource(
                $userId,
                'deposit',
                $income->bank_account_id ? (int) $income->bank_account_id : null,
                'credit',
                (float) $income->amount,
                (string) $income->date,
                $income,
                null,
                $income->reference ? "Income: {$income->reference}" : null
            );
        });

        return response()->json($income->refresh());
    }

    public function destroy(Request $request, Income $income): JsonResponse
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId, $income) {
            $this->ledger->upsertForSource($userId, 'deposit', null, 'credit', 0, now()->toDateString(), $income);
            $income->delete();
        });

        return response()->json(['message' => 'Deleted']);
    }
}
