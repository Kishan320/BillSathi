<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalVoucher;
use Illuminate\Http\Request;

class JournalVoucherController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            JournalVoucher::where('user_id', $request->user()->id)->orderByDesc('date')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'voucher_number' => 'nullable|string|max:100',
            'date'           => 'required|date',
            'description'    => 'nullable|string',
            'debit'          => 'required|numeric|min:0',
            'credit'         => 'required|numeric|min:0',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(JournalVoucher::create($data), 201);
    }

    public function show(Request $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        return response()->json($journalVoucher);
    }

    public function update(Request $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        $journalVoucher->update($request->validate([
            'voucher_number' => 'nullable|string|max:100',
            'date'           => 'sometimes|required|date',
            'description'    => 'nullable|string',
            'debit'          => 'sometimes|required|numeric|min:0',
            'credit'         => 'sometimes|required|numeric|min:0',
        ]));
        return response()->json($journalVoucher);
    }

    public function destroy(Request $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        $journalVoucher->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
