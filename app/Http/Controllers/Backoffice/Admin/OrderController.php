<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('orders.view');

        $orders = Order::query()
            ->with(['user', 'assignedAgent'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%")
                        ->orWhere('buyer_phone', 'like', "%{$search}%")
                        ->orWhere('buyer_email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('order_status'), fn ($q) => $q->where('order_status', $request->string('order_status')->toString()))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('agent_id'), fn ($q) => $q->where('assigned_agent_id', $request->integer('agent_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.orders.partials.table', [
                    'orders'  => $orders,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $orders->hasPages() ? (string) $orders->links() : '',
            ]);
        }

        $agents = Admin::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('backoffice.admin.orders.index', [
            'title'  => 'Orders Management',
            'orders' => $orders,
            'agents' => $agents,
        ]);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('orders.view');

        $orders = Order::onlyTrashed()
            ->with(['user', 'assignedAgent'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('buyer_name', 'like', "%{$search}%")
                        ->orWhere('buyer_phone', 'like', "%{$search}%")
                        ->orWhere('buyer_email', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.orders.partials.table', [
                    'orders'  => $orders,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $orders->hasPages() ? (string) $orders->links() : '',
            ]);
        }

        return view('backoffice.admin.orders.trash', [
            'title'  => 'Orders Trash Bin',
            'orders' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse|RedirectResponse
    {
        $order = $this->orderService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} created successfully.",
                'order'   => $order,
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function show(Request $request, Order $order): JsonResponse|View
    {
        $this->authorizeAction('orders.view');

        $order->load(['user', 'assignedAgent']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'order'   => [
                    'id'               => $order->id,
                    'order_number'     => $order->order_number,
                    'buyer_name'       => $order->buyer_name,
                    'buyer_phone'      => $order->buyer_phone,
                    'buyer_email'      => $order->buyer_email,
                    'shipping_address' => $order->shipping_address,
                    'city_or_area'     => $order->city_or_area,
                    'subtotal'         => number_format((float) $order->subtotal, 2),
                    'discount'         => number_format((float) $order->discount, 2),
                    'shipping'         => number_format((float) $order->shipping, 2),
                    'grand_total'      => number_format((float) $order->grand_total, 2),
                    'order_status'     => ucfirst($order->order_status),
                    'payment_status'   => ucfirst($order->payment_status),
                    'order_badge'      => $order->order_status_badge,
                    'payment_badge'    => $order->payment_status_badge,
                    'agent_name'       => $order->assignedAgent?->name ?? 'Unassigned',
                    'coupon_code'      => $order->coupon_code ?? 'None',
                    'customer_note'    => $order->customer_note ?: 'No note provided',
                    'internal_note'    => $order->internal_note ?: 'No internal notes',
                    'created_at'       => optional($order->created_at)->format('d M Y, h:i A'),
                    'is_guest'         => $order->isGuest(),
                ],
            ]);
        }

        return view('backoffice.admin.orders.show', compact('order'));
    }

    public function edit(Request $request, Order $order): JsonResponse
    {
        $this->authorizeAction('orders.update');

        return response()->json([
            'success' => true,
            'order'   => [
                'id'                => $order->id,
                'order_number'      => $order->order_number,
                'buyer_name'        => $order->buyer_name,
                'buyer_phone'       => $order->buyer_phone,
                'buyer_email'       => $order->buyer_email,
                'shipping_address'  => $order->shipping_address,
                'city_or_area'      => $order->city_or_area,
                'subtotal'          => $order->subtotal,
                'discount'          => $order->discount,
                'shipping'          => $order->shipping,
                'grand_total'       => $order->grand_total,
                'order_status'      => $order->order_status,
                'payment_status'    => $order->payment_status,
                'assigned_agent_id' => $order->assigned_agent_id,
                'customer_note'     => $order->customer_note,
                'internal_note'     => $order->internal_note,
            ],
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->orderService->update($order, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} updated successfully.",
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', "Order #{$order->order_number} updated successfully.");
    }

    public function destroy(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.update');
        $this->orderService->delete($order);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} moved to trash bin.",
            ]);
        }

        return back()->with('success', "Order #{$order->order_number} moved to trash.");
    }

    public function restore(Request $request, int $order): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.restore');
        $trashedOrder = Order::onlyTrashed()->findOrFail($order);
        $this->orderService->restore($trashedOrder);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$trashedOrder->order_number} restored successfully.",
            ]);
        }

        return back()->with('success', "Order restored successfully.");
    }

    public function forceDelete(Request $request, int $order): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.force-delete');
        $trashedOrder = Order::onlyTrashed()->findOrFail($order);
        $this->orderService->forceDelete($trashedOrder);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order permanently deleted.",
            ]);
        }

        return back()->with('success', "Order permanently deleted.");
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete'       => 'orders.update',
            'restore'      => 'orders.restore',
            'force-delete' => 'orders.force-delete',
            default        => null,
        };

        if (! $permission) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
            }
            return back()->withErrors(['action' => 'Invalid bulk action.']);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action'      => ['required', 'in:delete,restore,force-delete'],
            'order_ids'   => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
        ])->validate();

        $result = $this->orderService->bulk($validated['action'], $validated['order_ids']);
        $message = sprintf('%d order(s) processed.', $result['processed']);

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

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}