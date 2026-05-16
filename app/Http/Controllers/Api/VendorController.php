<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::where('user_id', $request->user()->id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('billing_name', 'like', "%{$request->search}%")
                  ->orWhere('mobile_number', 'like', "%{$request->search}%")
                  ->orWhere('tax_number', 'like', "%{$request->search}%")
                  ->orWhere('billing_address', 'like', "%{$request->search}%");
            });
        }

        $sortBy = $request->sort_by ?? 'billing_name';
        $sortDir = $request->sort_dir ?? 'asc';
        $perPage = $request->per_page ?? 10;

        return response()->json(
            $query->orderBy($sortBy, $sortDir)->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'billing_address' => 'required|string|max:255',
            'billing_address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'same_as_billing' => 'required|boolean',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_zip_code' => 'nullable|string|max:20',
        ]);

        $validated['user_id'] = $request->user()->id;
        $vendor = Vendor::create($validated);

        return response()->json($vendor, 201);
    }

    public function show(Request $request, Vendor $vendor)
    {
        abort_if($vendor->user_id !== $request->user()->id, 403);
        return response()->json($vendor);
    }

    public function update(Request $request, Vendor $vendor)
    {
        abort_if($vendor->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'billing_address' => 'required|string|max:255',
            'billing_address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'same_as_billing' => 'required|boolean',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_zip_code' => 'nullable|string|max:20',
        ]);

        $vendor->update($validated);
        return response()->json($vendor);
    }

    public function destroy(Request $request, Vendor $vendor)
    {
        abort_if($vendor->user_id !== $request->user()->id, 403);
        $vendor->delete();
        return response()->json(['message' => 'Vendor deleted successfully']);
    }
}
