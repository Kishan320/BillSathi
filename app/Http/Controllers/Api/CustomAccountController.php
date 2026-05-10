<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomAccount;
use Illuminate\Http\Request;

class CustomAccountController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(CustomAccount::where('user_id', $request->user()->id)->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'description'     => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        return response()->json(CustomAccount::create($data), 201);
    }

    public function show(Request $request, CustomAccount $customAccount)
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        return response()->json($customAccount);
    }

    public function update(Request $request, CustomAccount $customAccount)
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        $customAccount->update($request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'type'            => 'in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'description'     => 'nullable|string',
        ]));
        return response()->json($customAccount);
    }

    public function destroy(Request $request, CustomAccount $customAccount)
    {
        abort_if($customAccount->user_id !== $request->user()->id, 403);
        $customAccount->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
