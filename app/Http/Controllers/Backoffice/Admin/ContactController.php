<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contactService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('contacts.view');

        $contacts = Contact::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('map_url', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return $this->tableResponse($contacts, false);
        }

        return view('backoffice.admin.contacts.index', [
            'title' => 'Contact Information',
            'breadcrumb' => [['text' => 'Contacts', 'url' => null]],
            'contacts' => $contacts,
        ]);
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.create');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'contact' => $this->emptyPayload(),
            ]);
        }

        return redirect()->route('admin.contacts.index');
    }

    public function store(StoreContactRequest $request): JsonResponse|RedirectResponse
    {
        $contact = $this->contactService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information created successfully.',
                'contact' => $this->payload($contact),
            ]);
        }

        return redirect()->route('admin.contacts.index')->with('success', 'Contact information created successfully.');
    }

    public function show(Request $request, Contact $contact): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.view');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'contact' => $this->payload($contact),
            ]);
        }

        return redirect()->route('admin.contacts.index', ['show' => $contact->getKey()]);
    }

    public function edit(Request $request, Contact $contact): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.update');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'contact' => $this->payload($contact),
            ]);
        }

        return redirect()->route('admin.contacts.index', ['edit' => $contact->getKey()]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse|RedirectResponse
    {
        $contact = $this->contactService->update($contact, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information updated successfully.',
                'contact' => $this->payload($contact),
            ]);
        }

        return redirect()->route('admin.contacts.index')->with('success', 'Contact information updated successfully.');
    }

    public function destroy(Request $request, Contact $contact): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.delete');
        $this->contactService->delete($contact);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information moved to trash.',
            ]);
        }

        return back()->with('success', 'Contact information moved to trash.');
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('contacts.view');

        $contacts = Contact::onlyTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return $this->tableResponse($contacts, true);
        }

        return view('backoffice.admin.contacts.trash', [
            'title' => 'Contact Trash',
            'breadcrumb' => [
                ['text' => 'Contacts', 'url' => route('admin.contacts.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'contacts' => $contacts,
        ]);
    }

    public function restore(Request $request, int $contact): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.restore');
        $trashed = Contact::onlyTrashed()->findOrFail($contact);
        $this->contactService->restore($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information restored successfully.',
            ]);
        }

        return back()->with('success', 'Contact information restored successfully.');
    }

    public function forceDelete(Request $request, int $contact): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contacts.force-delete');
        $trashed = Contact::onlyTrashed()->findOrFail($contact);
        $this->contactService->forceDelete($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information permanently deleted.',
            ]);
        }

        return back()->with('success', 'Contact information permanently deleted.');
    }

    private function tableResponse($contacts, bool $isTrash): JsonResponse
    {
        return response()->json([
            'success' => true,
            'html' => view('backoffice.admin.contacts.partials.table', [
                'contacts' => $contacts,
                'isTrash' => $isTrash,
            ])->render(),
            'pagination' => $contacts->hasPages() ? (string) $contacts->links() : '',
        ]);
    }

    /** @return array<string, mixed> */
    private function emptyPayload(): array
    {
        return [
            'id' => null,
            'name' => '',
            'email' => '',
            'phone' => '',
            'map_url' => '',
            'status' => 'active',
        ];
    }

    /** @return array<string, mixed> */
    private function payload(Contact $contact): array
    {
        return [
            'id' => $contact->getKey(),
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'map_url' => $contact->map_url,
            'status' => $contact->status,
            'created_at' => $contact->created_at?->format('d M Y, h:i A'),
            'updated_at' => $contact->updated_at?->format('d M Y, h:i A'),
        ];
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
