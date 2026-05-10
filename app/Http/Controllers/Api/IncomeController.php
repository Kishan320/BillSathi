<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IncomeRequest;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);
        if ($request->search) $query->where('reference', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    public function store(IncomeRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(Income::create($data), 201);
    }

    public function show(Request $request, Income $income)
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        return response()->json($income->load(['contact', 'bankAccount']));
    }

    public function update(IncomeRequest $request, Income $income)
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        $data = $request->validated();
        $income->update($data);
        return response()->json($income);
    }

    public function destroy(Request $request, Income $income)
    {
        abort_if($income->user_id !== $request->user()->id, 403);
        $income->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
