<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BankTransferRequest;
use App\Models\BankTransfer;
use Illuminate\Http\Request;

class BankTransferController extends Controller
{
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
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(BankTransfer::create($data), 201);
    }

    public function show(Request $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);
        return response()->json($bankTransfer->load(['fromAccount', 'toAccount']));
    }

    public function update(BankTransferRequest $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);
        $data = $request->validated();
        $bankTransfer->update($data);
        return response()->json($bankTransfer);
    }

    public function destroy(Request $request, BankTransfer $bankTransfer)
    {
        abort_if($bankTransfer->user_id !== $request->user()->id, 403);
        $bankTransfer->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
