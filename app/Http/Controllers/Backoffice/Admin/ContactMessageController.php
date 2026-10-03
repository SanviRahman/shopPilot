<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessage\UpdateContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(private readonly ContactMessageService $contactMessageService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('contact-messages.view');
        $filters = ['search' => $request->string('search')->trim()->toString(), 'status' => $request->string('status')->trim()->toString()];
        $messages = $this->contactMessageService->paginate($filters);
        if ($request->ajax()) return $this->tableResponse($messages, false);
        return view('backoffice.admin.contact-messages.index', ['title' => 'Contact Messages', 'breadcrumb' => [['text' => 'Contact Messages', 'url' => null]], 'messages' => $messages, 'filters' => $filters]);
    }

    public function show(Request $request, ContactMessage $contactMessage): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contact-messages.view');
        if ($request->ajax()) return response()->json(['success' => true, 'message' => $this->payload($contactMessage)]);
        return redirect()->route('admin.contact-messages.index', ['show' => $contactMessage->getKey()]);
    }

    public function edit(Request $request, ContactMessage $contactMessage): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contact-messages.update');
        if ($request->ajax()) return response()->json(['success' => true, 'message' => $this->payload($contactMessage)]);
        return redirect()->route('admin.contact-messages.index', ['edit' => $contactMessage->getKey()]);
    }

    public function update(UpdateContactMessageRequest $request, ContactMessage $contactMessage): JsonResponse|RedirectResponse
    {
        $message = $this->contactMessageService->updateStatus($contactMessage, $request->validated('status'));
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Contact message status updated successfully.', 'contact_message' => $this->payload($message)]);
        return back()->with('success', 'Contact message status updated successfully.');
    }

    public function destroy(Request $request, ContactMessage $contactMessage): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contact-messages.delete');
        $this->contactMessageService->delete($contactMessage);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Contact message moved to trash.']);
        return back()->with('success', 'Contact message moved to trash.');
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('contact-messages.view');
        $filters = ['search' => $request->string('search')->trim()->toString()];
        $messages = $this->contactMessageService->paginate($filters, true);
        if ($request->ajax()) return $this->tableResponse($messages, true);
        return view('backoffice.admin.contact-messages.trash', ['title' => 'Contact Message Trash', 'breadcrumb' => [['text' => 'Contact Messages', 'url' => route('admin.contact-messages.index')], ['text' => 'Trash', 'url' => null]], 'messages' => $messages, 'filters' => $filters]);
    }

    public function restore(Request $request, int $contactMessage): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contact-messages.restore');
        $message = ContactMessage::onlyTrashed()->findOrFail($contactMessage);
        $this->contactMessageService->restore($message);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Contact message restored successfully.']);
        return back()->with('success', 'Contact message restored successfully.');
    }

    public function forceDelete(Request $request, int $contactMessage): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('contact-messages.force-delete');
        $message = ContactMessage::onlyTrashed()->findOrFail($contactMessage);
        $this->contactMessageService->forceDelete($message);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Contact message permanently deleted.']);
        return back()->with('success', 'Contact message permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'mark-read', 'mark-resolved' => 'contact-messages.update',
            'delete' => 'contact-messages.delete',
            'restore' => 'contact-messages.restore',
            'force-delete' => 'contact-messages.force-delete',
            default => null,
        };
        if (! $permission) return $request->ajax() ? response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422) : back()->withErrors(['action' => 'Invalid bulk action.']);
        $this->authorizeAction($permission);
        $validated = Validator::make($request->all(), ['action' => ['required', 'in:mark-read,mark-resolved,delete,restore,force-delete'], 'message_ids' => ['required', 'array', 'min:1'], 'message_ids.*' => ['integer']])->validate();
        $processed = $this->contactMessageService->bulkAction($validated['action'], $validated['message_ids']);
        $message = sprintf('%d contact message(s) processed.', $processed);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => $message]);
        return back()->with('success', $message);
    }

    private function tableResponse($messages, bool $isTrash): JsonResponse
    {
        return response()->json(['success' => true, 'html' => view('backoffice.admin.contact-messages.partials.table', ['messages' => $messages, 'isTrash' => $isTrash])->render(), 'pagination' => $messages->hasPages() ? (string) $messages->links() : '']);
    }

    private function payload(ContactMessage $message): array
    {
        return ['id' => $message->getKey(), 'name' => $message->name, 'email' => $message->email, 'phone' => $message->phone, 'subject' => $message->subject, 'message' => $message->message, 'status' => $message->status, 'ip_address' => $message->ip_address, 'user_agent' => $message->user_agent, 'read_at' => $message->read_at?->format('d M Y, h:i A'), 'resolved_at' => $message->resolved_at?->format('d M Y, h:i A'), 'created_at' => $message->created_at?->format('d M Y, h:i A'), 'updated_at' => $message->updated_at?->format('d M Y, h:i A')];
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) throw new AuthorizationException('You are not authorized to perform this action.');
    }
}
