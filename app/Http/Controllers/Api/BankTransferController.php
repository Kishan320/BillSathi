<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BankTransferRequest;
use App\Models\BankTransfer;
use App\Services\BankLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BankTransferController extends Controller
{
    public function __construct(private BankLedgerService $ledger) {}

    public function index(Request $request)
    {
        return response()->json(
            BankTransfer::with(['fromAccount', 'toAccount'])
                ->where('user_id', $request->user()->id)
                ->orderByDesc('date')->paginate(20)
        );
    }

    public function store(BankTransferRequest $request)
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;

        $transfer = DB::transaction(function () use ($userId, $data) {
            $transfer = BankTransfer::create($data);

            $amount = (float) $transfer->amount;
            $date = (string) $transfer->date;

            $this->ledger->upsertForSource($userId, 'transfer', $transfer->from_account_id, 'debit', $amount, $date, $transfer, 'from');
            $this->ledger->upsertForSource($userId, 'transfer', $transfer->to_account_id, 'credit', $amount, $date, $transfer, 'to');

            return $transfer;
        });

        return response()->json($transfer->load(['fromAccount', 'toAccount']), 201);
    }

    public function show(Request $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);
        return response()->json($bankTransfer->load(['fromAccount', 'toAccount']));
    }

    public function update(BankTransferRequest $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        $data = $request->validated();

        DB::transaction(function () use ($userId, $bankTransfer, $data) {
            $bankTransfer->update($data);
            $bankTransfer->refresh();

            $amount = (float) $bankTransfer->amount;
            $date = (string) $bankTransfer->date;

            $this->ledger->upsertForSource($userId, 'transfer', $bankTransfer->from_account_id, 'debit', $amount, $date, $bankTransfer, 'from');
            $this->ledger->upsertForSource($userId, 'transfer', $bankTransfer->to_account_id, 'credit', $amount, $date, $bankTransfer, 'to');
        });

        return response()->json($bankTransfer->refresh()->load(['fromAccount', 'toAccount']));
    }

    public function destroy(Request $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);

        $userId = $request->user()->id;
        DB::transaction(function () use ($userId, $bankTransfer) {
            $this->ledger->upsertForSource($userId, 'transfer', null, 'debit', 0, now()->toDateString(), $bankTransfer, 'from');
            $this->ledger->upsertForSource($userId, 'transfer', null, 'credit', 0, now()->toDateString(), $bankTransfer, 'to');
            $bankTransfer->delete();
        });

        return response()->json(['message' => 'Deleted']);
    }
}
