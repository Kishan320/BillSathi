<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SettlementRequest;
use App\Models\Settlement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json(
            Settlement::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id)
                ->orderByDesc('date')->paginate($perPage)
        );
    }

    public function store(SettlementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Settlement::create($data), 201);
    }

    public function show(Request $request, Settlement $settlement): JsonResponse
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        return response()->json($settlement->load(['contact', 'bankAccount']));
    }

    public function update(SettlementRequest $request, Settlement $settlement): JsonResponse
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        $settlement->update($request->validated());
        return response()->json($settlement);
    }

    public function destroy(Request $request, Settlement $settlement): JsonResponse
    {
        abort_if($settlement->user_id !== $request->user()->id, 403);
        $settlement->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
