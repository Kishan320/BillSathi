<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::where('user_id', $request->user()->id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', (int) $request->status);
        }

        $sortBy  = $request->sort_by  ?? 'name';
        $sortDir = $request->sort_dir ?? 'asc';
        $perPage = $request->per_page ?? 10;

        return response()->json(
            $query->orderBy($sortBy, $sortDir)->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'zip_code'       => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'status'         => 'required|in:0,1',
            'notes'          => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        return response()->json(Warehouse::create($validated), 201);
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        abort_if($warehouse->user_id !== $request->user()->id, 403);
        return response()->json($warehouse);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        abort_if($warehouse->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:100',
            'zip_code'       => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'status'         => 'required|in:0,1',
            'notes'          => 'nullable|string',
        ]);

        $warehouse->update($validated);
        return response()->json($warehouse);
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        abort_if($warehouse->user_id !== $request->user()->id, 403);
        $warehouse->delete();
        return response()->json(['message' => 'Warehouse deleted successfully']);
    }
}
