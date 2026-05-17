<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoanRequest;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json(
            Loan::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id)
                ->orderByDesc('date')->paginate($perPage)
        );
    }

    public function store(LoanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Loan::create($data), 201);
    }

    public function show(Request $request, Loan $loan): JsonResponse
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        return response()->json($loan->load(['contact', 'bankAccount']));
    }

    public function update(LoanRequest $request, Loan $loan): JsonResponse
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        $loan->update($request->validated());
        return response()->json($loan);
    }

    public function destroy(Request $request, Loan $loan): JsonResponse
    {
        abort_if($loan->user_id !== $request->user()->id, 403);
        $loan->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
