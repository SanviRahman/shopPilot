<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentMethodRequest;
use App\Http\Requests\Admin\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct(private readonly PaymentMethodService $paymentMethodService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('payment-methods.view');

        $methods = PaymentMethod::onlyTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%"));
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.payment-methods.partials.table', [
                    'methods' => $methods,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $methods->hasPages() ? (string) $methods->links() : '',
            ]);
        }

        return view('backoffice.admin.payment-methods.trash', [
            'title' => 'Payment Method Trash',
            'breadcrumb' => [
                ['text' => 'Payment Methods', 'url' => route('admin.payment-methods.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'methods' => $methods,
        ]);
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse|RedirectResponse
    {
        $method = $this->paymentMethodService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully.',
                'method' => $method,
            ]);
        }

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method created successfully.');
    }

    public function show(Request $request, PaymentMethod $paymentMethod): View|JsonResponse
    {
        $this->authorizeAction('payment-methods.view');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'method' => [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'code' => strtoupper($paymentMethod->code),
                    'account_number' => $paymentMethod->account_number,
                    'account_type' => ucfirst($paymentMethod->account_type),
                    'instruction' => $paymentMethod->instruction,
                    'status' => ucfirst($paymentMethod->status),
                    'created_at' => optional($paymentMethod->created_at)->format('d M Y, h:i A'),
                    'updated_at' => optional($paymentMethod->updated_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.payment-methods.show', ['method' => $paymentMethod]);
    }

    public function edit(Request $request, PaymentMethod $paymentMethod): JsonResponse|View
    {
        $this->authorizeAction('payment-methods.manage');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'method' => [
                    'id' => $paymentMethod->id,
                    'name' => $paymentMethod->name,
                    'code' => $paymentMethod->code,
                    'account_number' => $paymentMethod->account_number,
                    'account_type' => $paymentMethod->account_type,
                    'instruction' => $paymentMethod->instruction,
                    'status' => $paymentMethod->status,
                ],
            ]);
        }

        return $this->indexView($request, $paymentMethod, true);
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse|RedirectResponse
    {
        $this->paymentMethodService->update($paymentMethod, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully.',
            ]);
        }

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method updated successfully.');
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payment-methods.delete');
        $this->paymentMethodService->delete($paymentMethod);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Payment method moved to trash.');
    }

    public function restore(Request $request, int $paymentMethod): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payment-methods.restore');
        $trashedMethod = PaymentMethod::onlyTrashed()->findOrFail($paymentMethod);
        $this->paymentMethodService->restore($trashedMethod);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method restored successfully.',
            ]);
        }

        return back()->with('success', 'Payment method restored successfully.');
    }

    public function forceDelete(Request $request, int $paymentMethod): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('payment-methods.force-delete');
        $trashedMethod = PaymentMethod::onlyTrashed()->findOrFail($paymentMethod);
        $this->paymentMethodService->forceDelete($trashedMethod);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method permanently deleted.',
            ]);
        }

        return back()->with('success', 'Payment method permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'payment-methods.delete',
            'restore' => 'payment-methods.restore',
            'force-delete' => 'payment-methods.force-delete',
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
            'method_ids' => ['required', 'array', 'min:1'],
            'method_ids.*' => ['integer'],
        ])->validate();

        $result = $this->paymentMethodService->bulk($validated['action'], $validated['method_ids']);
        $message = sprintf('%d payment method(s) processed.', $result['processed']);

        if ($result['skipped'] > 0) {
            $message .= sprintf(' %d skipped.', $result['skipped']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?PaymentMethod $formMethod = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('payment-methods.view');

        $methods = PaymentMethod::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.payment-methods.partials.table', [
                    'methods' => $methods,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $methods->hasPages() ? (string) $methods->links() : '',
            ]);
        }

        return view('backoffice.admin.payment-methods.index', [
            'title' => 'Payment Methods Management',
            'breadcrumb' => [
                ['text' => 'Payment Methods', 'url' => null],
            ],
            'methods' => $methods,
            'formMethod' => $formMethod,
            'formMode' => $formMethod ? 'edit' : 'create',
            'openForm' => $openForm,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}