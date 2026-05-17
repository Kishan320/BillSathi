<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomAccountRequest;
use App\Models\CustomAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(CustomAccount::where('user_id', $request->user()->id)->get());
    }

    public function store(CustomAccountRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return response()->json(CustomAccount::create($data), 201);
    }

    public function show(Request $request, CustomAccount $customAccount): JsonResponse
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        return response()->json($customAccount);
    }

    public function update(CustomAccountRequest $request, CustomAccount $customAccount): JsonResponse
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        $customAccount->update($request->validated());
        return response()->json($customAccount);
    }

    public function destroy(Request $request, CustomAccount $customAccount): JsonResponse
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        $customAccount->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
