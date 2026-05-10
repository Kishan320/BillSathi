<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::where('user_id', $request->user()->id);
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('mobile', 'like', "%{$request->search}%");
            });
        }
        if ($request->type) $query->where('type', $request->type);
        return response()->json($query->orderBy('name')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:255',
            'gstin'                   => 'nullable|string|max:20',
            'pan'                     => 'nullable|string|max:20',
            'mobile'                  => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:255',
            'type'                    => 'in:customer,vendor,both',
            'due_in_days'             => 'nullable|integer|min:0',
            'currency'                => 'nullable|string|max:10',
            'billing_address1'        => 'nullable|string|max:255',
            'billing_address2'        => 'nullable|string|max:255',
            'billing_city'            => 'nullable|string|max:100',
            'billing_pincode'         => 'nullable|string|max:20',
            'billing_state'           => 'nullable|string|max:100',
            'billing_country'         => 'nullable|string|max:100',
            'shipping_same_as_billing'=> 'nullable|in:0,1',
            'shipping_address1'       => 'nullable|string|max:255',
            'shipping_address2'       => 'nullable|string|max:255',
            'shipping_city'           => 'nullable|string|max:100',
            'shipping_pincode'        => 'nullable|string|max:20',
            'shipping_state'          => 'nullable|string|max:100',
            'shipping_country'        => 'nullable|string|max:100',
            'opening_balance'         => 'nullable|numeric|min:0',
            'opening_balance_type'    => 'in:payable,receivable',
            'enable_customer_portal'  => 'nullable|in:0,1',
            'notes'                   => 'nullable|string|max:250',
        ]);

        $data['user_id'] = $request->user()->id;
        $contact = Contact::create($data);
        return response()->json($contact, 201);
    }

    public function show(Request $request, Contact $contact)
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        return response()->json($contact);
    }

    public function update(Request $request, Contact $contact)
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        $data = $request->validate([
            'name'                    => 'sometimes|required|string|max:255',
            'gstin'                   => 'nullable|string|max:20',
            'pan'                     => 'nullable|string|max:20',
            'mobile'                  => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:255',
            'type'                    => 'in:customer,vendor,both',
            'due_in_days'             => 'nullable|integer|min:0',
            'currency'                => 'nullable|string|max:10',
            'billing_address1'        => 'nullable|string|max:255',
            'billing_address2'        => 'nullable|string|max:255',
            'billing_city'            => 'nullable|string|max:100',
            'billing_pincode'         => 'nullable|string|max:20',
            'billing_state'           => 'nullable|string|max:100',
            'billing_country'         => 'nullable|string|max:100',
            'shipping_same_as_billing'=> 'nullable|in:0,1',
            'shipping_address1'       => 'nullable|string|max:255',
            'shipping_address2'       => 'nullable|string|max:255',
            'shipping_city'           => 'nullable|string|max:100',
            'shipping_pincode'        => 'nullable|string|max:20',
            'shipping_state'          => 'nullable|string|max:100',
            'shipping_country'        => 'nullable|string|max:100',
            'opening_balance'         => 'nullable|numeric|min:0',
            'opening_balance_type'    => 'in:payable,receivable',
            'enable_customer_portal'  => 'nullable|in:0,1',
            'notes'                   => 'nullable|string|max:250',
        ]);
        $contact->update($data);
        return response()->json($contact);
    }

    public function destroy(Request $request, Contact $contact)
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        $contact->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
