<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('customers.view');
        $customers = $this->query($request, true);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.customers.partials.table', compact('customers') + ['isTrash' => true])->render(),
                'pagination' => $customers->hasPages() ? (string) $customers->links() : '',
            ]);
        }

        return view('backoffice.admin.customers.trash', [
            'title' => 'Customer Trash',
            'breadcrumb' => [
                ['text' => 'Customers', 'url' => route('admin.customers.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'customers' => $customers,
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $this->customerService->create($request->validated());

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Customer created successfully.', 'customer' => $customer])
            : redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Request $request, User $customer): JsonResponse|View
    {
        $this->authorizeAction('customers.view');
        $customer->loadCount('orders')->load('roles');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => ucfirst($customer->status ?? 'active'),
                'orders_count' => $customer->orders_count,
                'created_at' => optional($customer->created_at)->format('d M Y, h:i A'),
                'updated_at' => optional($customer->updated_at)->format('d M Y, h:i A'),
            ]]);
        }

        return view('backoffice.admin.customers.show', compact('customer'));
    }

    public function edit(Request $request, User $customer): JsonResponse|View
    {
        $this->authorizeAction('customers.update');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => $customer->status ?? 'active',
            ]]);
        }

        return $this->indexView($request, $customer, true);
    }

    public function update(UpdateCustomerRequest $request, User $customer): JsonResponse|RedirectResponse
    {
        $this->customerService->update($customer, $request->validated());

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Customer updated successfully.'])
            : redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, User $customer): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('customers.delete');
        $this->customerService->delete($customer);

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Customer moved to trash successfully.'])
            : back()->with('success', 'Customer moved to trash successfully.');
    }

    public function restore(Request $request, int $customer): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('customers.restore');
        $record = User::onlyTrashed()->findOrFail($customer);
        $this->customerService->restore($record);

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Customer restored successfully.'])
            : back()->with('success', 'Customer restored successfully.');
    }

    public function forceDelete(Request $request, int $customer): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('customers.force-delete');
        $record = User::onlyTrashed()->findOrFail($customer);
        $this->customerService->forceDelete($record);

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Customer permanently deleted.'])
            : back()->with('success', 'Customer permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'customers.delete',
            'restore' => 'customers.restore',
            'force-delete' => 'customers.force-delete',
            default => null,
        };

        if (! $permission) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422)
                : back()->withErrors(['action' => 'Invalid bulk action.']);
        }

        $this->authorizeAction($permission);
        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer'],
        ])->validate();

        $result = $this->customerService->bulk($validated['action'], $validated['customer_ids']);
        $message = sprintf('%d customer(s) processed.', $result['processed']);

        return $request->ajax()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    private function indexView(Request $request, ?User $formCustomer = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('customers.view');
        $customers = $this->query($request, false);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.customers.partials.table', compact('customers') + ['isTrash' => false])->render(),
                'pagination' => $customers->hasPages() ? (string) $customers->links() : '',
            ]);
        }

        return view('backoffice.admin.customers.index', [
            'title' => 'Customer Management',
            'breadcrumb' => [['text' => 'Customers', 'url' => null]],
            'customers' => $customers,
            'formCustomer' => $formCustomer,
            'openForm' => $openForm,
        ]);
    }

    private function query(Request $request, bool $trash)
    {
        $query = $trash ? User::onlyTrashed() : User::query();

        return $query->withCount('orders')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when(! $trash && $request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc($trash ? 'deleted_at' : 'created_at')
            ->paginate(15)
            ->withQueryString();
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
