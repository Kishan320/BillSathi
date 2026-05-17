<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SaleRequest;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['contact'])->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json($query->orderByDesc('date')->paginate($perPage));
    }

    public function store(SaleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Sale::create($data), 201);
    }

    public function show(Request $request, Sale $sale): JsonResponse
    {
        abort_if($sale->user_id !== $request->user()->id, 403);
        return response()->json($sale->load(['contact', 'bankAccount']));
    }

    public function update(SaleRequest $request, Sale $sale): JsonResponse
    {
        abort_if($sale->user_id !== $request->user()->id, 403);
        $sale->update($request->validated());
        return response()->json($sale);
    }

    public function destroy(Request $request, Sale $sale): JsonResponse
    {
        abort_if($sale->user_id !== $request->user()->id, 403);
        $sale->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
