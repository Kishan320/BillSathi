<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactRequest;
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

    public function store(ContactRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $contact = Contact::create($data);
        return response()->json($contact, 201);
    }

    public function show(Request $request, Contact $contact)
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        return response()->json($contact);
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        $data = $request->validated();
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
