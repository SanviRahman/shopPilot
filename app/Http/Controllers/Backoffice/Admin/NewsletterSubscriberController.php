<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\StoreNewsletterSubscriberRequest;
use App\Http\Requests\Newsletter\UpdateNewsletterSubscriberRequest;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterSubscriberService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function __construct(private readonly NewsletterSubscriberService $newsletterSubscriberService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('newsletter-subscribers.view');
        $filters = ['search' => $request->string('search')->trim()->toString(), 'status' => $request->string('status')->trim()->toString()];
        $subscribers = $this->newsletterSubscriberService->paginate($filters);
        $stats = $this->newsletterSubscriberService->stats();
        if ($request->ajax()) return $this->tableResponse($subscribers, false, $stats);
        return view('backoffice.admin.newsletter-subscribers.index', ['title' => 'Newsletter Subscribers', 'breadcrumb' => [['text' => 'Newsletter Subscribers', 'url' => null]], 'subscribers' => $subscribers, 'filters' => $filters, 'stats' => $stats]);
    }

    public function store(StoreNewsletterSubscriberRequest $request): JsonResponse|RedirectResponse
    {
        $subscriber = $this->newsletterSubscriberService->create($request->validated());
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Newsletter subscriber created successfully.', 'subscriber' => $this->payload($subscriber)]);
        return back()->with('success', 'Newsletter subscriber created successfully.');
    }

    public function show(Request $request, NewsletterSubscriber $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('newsletter-subscribers.view');
        if ($request->ajax()) return response()->json(['success' => true, 'subscriber' => $this->payload($newsletterSubscriber)]);
        return redirect()->route('admin.newsletter-subscribers.index', ['show' => $newsletterSubscriber->getKey()]);
    }

    public function edit(Request $request, NewsletterSubscriber $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('newsletter-subscribers.update');
        if ($request->ajax()) return response()->json(['success' => true, 'subscriber' => $this->payload($newsletterSubscriber)]);
        return redirect()->route('admin.newsletter-subscribers.index', ['edit' => $newsletterSubscriber->getKey()]);
    }

    public function update(UpdateNewsletterSubscriberRequest $request, NewsletterSubscriber $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $subscriber = $this->newsletterSubscriberService->update($newsletterSubscriber, $request->validated());
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Newsletter subscriber updated successfully.', 'subscriber' => $this->payload($subscriber)]);
        return back()->with('success', 'Newsletter subscriber updated successfully.');
    }

    public function destroy(Request $request, NewsletterSubscriber $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('newsletter-subscribers.delete');
        $this->newsletterSubscriberService->delete($newsletterSubscriber);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Newsletter subscriber moved to trash.']);
        return back()->with('success', 'Newsletter subscriber moved to trash.');
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('newsletter-subscribers.view');
        $filters = ['search' => $request->string('search')->trim()->toString()];
        $subscribers = $this->newsletterSubscriberService->paginate($filters, true);
        $stats = $this->newsletterSubscriberService->stats();
        if ($request->ajax()) return $this->tableResponse($subscribers, true, $stats);
        return view('backoffice.admin.newsletter-subscribers.trash', ['title' => 'Newsletter Trash', 'breadcrumb' => [['text' => 'Newsletter Subscribers', 'url' => route('admin.newsletter-subscribers.index')], ['text' => 'Trash', 'url' => null]], 'subscribers' => $subscribers, 'filters' => $filters, 'stats' => $stats]);
    }

    public function restore(Request $request, int $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('newsletter-subscribers.restore');
        $subscriber = NewsletterSubscriber::onlyTrashed()->findOrFail($newsletterSubscriber);
        $this->newsletterSubscriberService->restore($subscriber);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Newsletter subscriber restored successfully.']);
        return back()->with('success', 'Newsletter subscriber restored successfully.');
    }

    public function forceDelete(Request $request, int $newsletterSubscriber): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('newsletter-subscribers.force-delete');
        $subscriber = NewsletterSubscriber::onlyTrashed()->findOrFail($newsletterSubscriber);
        $this->newsletterSubscriberService->forceDelete($subscriber);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Newsletter subscriber permanently deleted.']);
        return back()->with('success', 'Newsletter subscriber permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) { 'subscribe', 'unsubscribe' => 'newsletter-subscribers.update', 'delete' => 'newsletter-subscribers.delete', 'restore' => 'newsletter-subscribers.restore', 'force-delete' => 'newsletter-subscribers.force-delete', default => null };
        if (! $permission) return $request->ajax() ? response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422) : back()->withErrors(['action' => 'Invalid bulk action.']);
        $this->authorizeAction($permission);
        $validated = Validator::make($request->all(), ['action' => ['required', 'in:subscribe,unsubscribe,delete,restore,force-delete'], 'subscriber_ids' => ['required', 'array', 'min:1'], 'subscriber_ids.*' => ['integer']])->validate();
        $processed = $this->newsletterSubscriberService->bulkAction($validated['action'], $validated['subscriber_ids']);
        $message = sprintf('%d newsletter subscriber(s) processed.', $processed);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => $message]);
        return back()->with('success', $message);
    }

    private function tableResponse($subscribers, bool $isTrash, array $stats): JsonResponse
    {
        return response()->json(['success' => true, 'html' => view('backoffice.admin.newsletter-subscribers.partials.table', ['subscribers' => $subscribers, 'isTrash' => $isTrash])->render(), 'pagination' => $subscribers->hasPages() ? (string) $subscribers->links() : '', 'stats' => $stats]);
    }

    private function payload(NewsletterSubscriber $subscriber): array
    {
        return ['id' => $subscriber->getKey(), 'email' => $subscriber->email, 'status' => $subscriber->status, 'subscribed_at' => $subscriber->subscribed_at?->format('d M Y, h:i A'), 'unsubscribed_at' => $subscriber->unsubscribed_at?->format('d M Y, h:i A'), 'created_at' => $subscriber->created_at?->format('d M Y, h:i A'), 'updated_at' => $subscriber->updated_at?->format('d M Y, h:i A')];
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) throw new AuthorizationException('You are not authorized to perform this action.');
    }
}
