<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Services\PaymentSubmissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentSubmissionController extends Controller
{
    public function __construct(private readonly PaymentSubmissionService $paymentService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('payments.view');

        $payments = PaymentSubmission::onlyTrashed()
            ->with(['order', 'paymentMethod', 'verifier'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%")));
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.payments.partials.table', [
                    'payments' => $payments,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $payments->hasPages() ? (string) $payments->links() : '',
            ]);
        }

        return view('backoffice.admin.payments.trash', [
            'title' => 'Payment Submissions Trash',
            'breadcrumb' => [
                ['text' => 'Payment Submissions', 'url' => route('admin.payments.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'payments' => $payments,
        ]);
    }

    public function show(Request $request, PaymentSubmission $payment): View|JsonResponse
    {
        $this->authorizeAction('payments.view');
        $payment->load(['order', 'paymentMethod', 'verifier']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'order_number' => $payment->order?->order_number,
                    'method_name' => $payment->paymentMethod?->name,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => number_format($payment->amount, 2),
                    'status' => ucfirst($payment->status),
                    'verifier_name' => $payment->verifier?->name ?? 'N/A',
                    'verified_at' => optional($payment->verified_at)->format('d M Y, h:i A'),
                    'rejection_note' => $payment->rejection_note,
                    'created_at' => optional($payment->created_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, PaymentSubmission $payment): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payments.verify');
        $this->paymentService->verify($payment);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully.',
            ]);
        }

        return back()->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, PaymentSubmission $payment): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payments.reject');

        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ]);

        $this->paymentService->reject($payment, $validated['rejection_note']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment rejected successfully.',
            ]);
        }

        return back()->with('success', 'Payment rejected successfully.');
    }

    public function destroy(Request $request, PaymentSubmission $payment): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payments.verify');
        $this->paymentService->delete($payment);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment submission moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Payment submission moved to trash.');
    }

    public function restore(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payments.verify');
        $payment = PaymentSubmission::onlyTrashed()->findOrFail($id);
        $this->paymentService->restore($payment);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment submission restored successfully.',
            ]);
        }

        return back()->with('success', 'Payment submission restored successfully.');
    }

    public function forceDelete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payments.verify');
        $payment = PaymentSubmission::onlyTrashed()->findOrFail($id);
        $this->paymentService->forceDelete($payment);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment submission permanently deleted.',
            ]);
        }

        return back()->with('success', 'Payment submission permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete', 'restore', 'force-delete' => 'payments.verify',
            default => null,
        };

        if (! $permission) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
            }
            return back()->withErrors(['action' => 'Invalid bulk action.']);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['integer'],
        ])->validate();

        $result = $this->paymentService->bulk($validated['action'], $validated['payment_ids']);
        $message = sprintf('%d payment submission(s) processed.', $result['processed']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request): View|JsonResponse
    {
        $this->authorizeAction('payments.view');

        $payments = PaymentSubmission::query()
            ->with(['order', 'paymentMethod', 'verifier'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.payments.partials.table', [
                    'payments' => $payments,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $payments->hasPages() ? (string) $payments->links() : '',
            ]);
        }

        return view('backoffice.admin.payments.index', [
            'title' => 'Payment Submissions Management',
            'breadcrumb' => [
                ['text' => 'Payments', 'url' => null],
            ],
            'payments' => $payments,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
