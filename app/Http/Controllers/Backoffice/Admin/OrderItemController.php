<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderItemRequest;
use App\Http\Requests\Admin\UpdateOrderItemRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderItemService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderItemController extends Controller
{
    public function __construct(private readonly OrderItemService $orderItemService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $admin = $request->user('admin');
        $this->authorizeAction('orders.view');

        $orderItems = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->accessibleToAdmin($admin))
            ->with(['order', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->integer('order_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.order-items.partials.table', [
                    'orderItems' => $orderItems,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $orderItems->hasPages() ? (string) $orderItems->links() : '',
            ]);
        }

        $orders = Order::query()
            ->accessibleToAdmin($admin)
            ->latest()
            ->limit(100)
            ->get(['id', 'order_number']);

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'regular_price', 'sale_price']);

        return view('backoffice.admin.order-items.index', [
            'title' => 'Order Items Management',
            'orderItems' => $orderItems,
            'orders' => $orders,
            'products' => $products,
        ]);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $admin = $request->user('admin');
        $this->authorizeAction('orders.view');

        $orderItems = OrderItem::onlyTrashed()
            ->whereHas('order', fn ($query) => $query->accessibleToAdmin($admin))
            ->with(['order', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.order-items.partials.table', [
                    'orderItems' => $orderItems,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $orderItems->hasPages() ? (string) $orderItems->links() : '',
            ]);
        }

        return view('backoffice.admin.order-items.trash', [
            'title' => 'Order Items Trash Bin',
            'orderItems' => $orderItems,
        ]);
    }

    public function store(StoreOrderItemRequest $request): JsonResponse|RedirectResponse
    {
        $item = $this->orderItemService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Item '{$item->product_name}' added to Order successfully.",
            ]);
        }

        return redirect()->route('admin.order-items.index')->with('success', 'Item added successfully.');
    }

    public function show(Request $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        $orderItem->load(['order', 'product']);
        abort_unless($orderItem->order, 404);
        Gate::forUser($request->user('admin'))->authorize('view', $orderItem->order);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $orderItem->id,
                    'order_number' => $orderItem->order?->order_number ?? 'N/A',
                    'buyer_name' => $orderItem->order?->buyer_name ?? 'N/A',
                    'product_name' => $orderItem->product_name,
                    'sku' => $orderItem->sku,
                    'unit_price' => number_format((float) $orderItem->unit_price, 2),
                    'quantity' => $orderItem->quantity,
                    'line_total' => number_format((float) $orderItem->line_total, 2),
                    'created_at' => optional($orderItem->created_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return redirect()->route('admin.order-items.index');
    }

    public function edit(Request $request, OrderItem $orderItem): JsonResponse
    {
        $orderItem->loadMissing('order');
        abort_unless($orderItem->order, 404);
        Gate::forUser($request->user('admin'))->authorize('update', $orderItem->order);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $orderItem->id,
                'order_id' => $orderItem->order_id,
                'product_id' => $orderItem->product_id,
                'product_name' => $orderItem->product_name,
                'sku' => $orderItem->sku,
                'unit_price' => $orderItem->unit_price,
                'quantity' => $orderItem->quantity,
                'line_total' => $orderItem->line_total,
            ],
        ]);
    }

    public function update(UpdateOrderItemRequest $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        $this->orderItemService->update($orderItem, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order item updated successfully.',
            ]);
        }

        return redirect()->route('admin.order-items.index')->with('success', 'Order item updated successfully.');
    }

    public function destroy(Request $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        $orderItem->loadMissing('order');
        abort_unless($orderItem->order, 404);
        Gate::forUser($request->user('admin'))->authorize('delete', $orderItem->order);
        $this->orderItemService->delete($orderItem);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order item moved to trash.',
            ]);
        }

        return back()->with('success', 'Order item moved to trash.');
    }

    public function restore(Request $request, int $orderItem): JsonResponse|RedirectResponse
    {
        $trashed = OrderItem::onlyTrashed()->with('order')->findOrFail($orderItem);
        abort_unless($trashed->order, 404);
        Gate::forUser($request->user('admin'))->authorize('restore', $trashed->order);
        $this->orderItemService->restore($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order item restored and order totals recalculated.',
            ]);
        }

        return back()->with('success', 'Order item restored successfully.');
    }

    public function forceDelete(Request $request, int $orderItem): JsonResponse|RedirectResponse
    {
        $trashed = OrderItem::onlyTrashed()->with('order')->findOrFail($orderItem);
        abort_unless($trashed->order, 404);
        Gate::forUser($request->user('admin'))->authorize('forceDelete', $trashed->order);
        $this->orderItemService->forceDelete($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order item permanently deleted.',
            ]);
        }

        return back()->with('success', 'Order item permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'orders.delete',
            'restore' => 'orders.restore',
            'force-delete' => 'orders.force-delete',
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
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
        ])->validate();

        $result = $this->orderItemService->bulk($validated['action'], $validated['item_ids']);
        $message = sprintf('%d order item(s) processed.', $result['processed']);

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
