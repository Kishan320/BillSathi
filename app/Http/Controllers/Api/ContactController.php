<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::where('user_id', $request->user()->id);
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return response()->json($query->orderBy('name')->paginate($perPage));
    }

    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $contact = Contact::create($data);
        return response()->json($contact, 201);
    }

    public function show(Request $request, Contact $contact): JsonResponse
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        return response()->json($contact);
    }

    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        $data = $request->validated();
        $contact->update($data);
        return response()->json($contact);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        abort_if($contact->user_id !== $request->user()->id, 403);
        $contact->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
