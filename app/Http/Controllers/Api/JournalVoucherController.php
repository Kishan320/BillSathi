<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JournalVoucherRequest;
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

    public function store(JournalVoucherRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(JournalVoucher::create($data), 201);
    }

    public function show(Request $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        return response()->json($journalVoucher);
    }

    public function update(JournalVoucherRequest $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        $journalVoucher->update($request->validated());
        return response()->json($journalVoucher);
    }

    public function destroy(Request $request, JournalVoucher $journalVoucher)
    {
        abort_if($journalVoucher->user_id !== $request->user()->id, 403);
        $journalVoucher->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
