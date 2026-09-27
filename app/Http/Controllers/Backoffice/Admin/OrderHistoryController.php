<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderHistoryRequest;
use App\Http\Requests\Admin\UpdateOrderHistoryRequest;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Services\OrderHistoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderHistoryController extends Controller
{
    public function __construct(private readonly OrderHistoryService $orderHistoryService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('orders.view');

        $histories = OrderHistory::query()
            ->with(['order', 'admin', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                        ->orWhere('from_status', 'like', "%{$search}%")
                        ->orWhere('to_status', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('admin', fn ($aq) => $aq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->integer('order_id')))
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                $q->where(fn ($sub) => $sub->where('from_status', $status)->orWhere('to_status', $status));
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.order-histories.partials.table', [
                    'histories' => $histories,
                    'isTrash'   => false,
                ])->render(),
                'pagination' => $histories->hasPages() ? (string) $histories->links() : '',
            ]);
        }

        $orders = Order::query()->latest()->limit(100)->get(['id', 'order_number']);

        return view('backoffice.admin.order-histories.index', [
            'title'     => 'Order Audit History',
            'histories' => $histories,
            'orders'    => $orders,
        ]);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('orders.view');

        $histories = OrderHistory::onlyTrashed()
            ->with(['order', 'admin', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.order-histories.partials.table', [
                    'histories' => $histories,
                    'isTrash'   => true,
                ])->render(),
                'pagination' => $histories->hasPages() ? (string) $histories->links() : '',
            ]);
        }

        return view('backoffice.admin.order-histories.trash', [
            'title'     => 'Order Histories Trash Bin',
            'histories' => $histories,
        ]);
    }

    public function store(StoreOrderHistoryRequest $request): JsonResponse|RedirectResponse
    {
        $history = $this->orderHistoryService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Audit note added to Order #{$history->order?->order_number}.",
            ]);
        }

        return redirect()->route('admin.order-histories.index')->with('success', 'Audit note added successfully.');
    }

    public function show(Request $request, OrderHistory $orderHistory): JsonResponse|View
    {
        $this->authorizeAction('orders.view');

        $orderHistory->load(['order', 'admin', 'user']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'history' => [
                    'id'               => $orderHistory->id,
                    'order_number'     => $orderHistory->order?->order_number ?? 'N/A',
                    'buyer_name'       => $orderHistory->order?->buyer_name ?? 'N/A',
                    'actor_badge'      => $orderHistory->actor_badge,
                    'transition_badge' => $orderHistory->status_transition_badge,
                    'from_status'      => ucfirst($orderHistory->from_status ?? 'N/A'),
                    'to_status'        => ucfirst($orderHistory->to_status ?? 'N/A'),
                    'note'             => $orderHistory->note,
                    'created_at'       => optional($orderHistory->created_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.order-histories.show', compact('orderHistory'));
    }

    public function edit(Request $request, OrderHistory $orderHistory): JsonResponse
    {
        $this->authorizeAction('orders.update');

        return response()->json([
            'success' => true,
            'history' => [
                'id'           => $orderHistory->id,
                'order_number' => $orderHistory->order?->order_number ?? 'N/A',
                'note'         => $orderHistory->note,
            ],
        ]);
    }

    public function update(UpdateOrderHistoryRequest $request, OrderHistory $orderHistory): JsonResponse|RedirectResponse
    {
        $this->orderHistoryService->update($orderHistory, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Order history note updated successfully.",
            ]);
        }

        return redirect()->route('admin.order-histories.index')->with('success', 'History note updated successfully.');
    }

    public function destroy(Request $request, OrderHistory $orderHistory): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.update');
        $this->orderHistoryService->delete($orderHistory);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "History record moved to trash.",
            ]);
        }

        return back()->with('success', 'History record moved to trash.');
    }

    public function restore(Request $request, int $orderHistory): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.restore');
        $trashed = OrderHistory::onlyTrashed()->findOrFail($orderHistory);
        $this->orderHistoryService->restore($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "History record restored successfully.",
            ]);
        }

        return back()->with('success', 'History record restored successfully.');
    }

    public function forceDelete(Request $request, int $orderHistory): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('orders.force-delete');
        $trashed = OrderHistory::onlyTrashed()->findOrFail($orderHistory);
        $this->orderHistoryService->forceDelete($trashed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "History record permanently deleted.",
            ]);
        }

        return back()->with('success', 'History record permanently deleted.');
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
            'action'        => ['required', 'in:delete,restore,force-delete'],
            'history_ids'   => ['required', 'array', 'min:1'],
            'history_ids.*' => ['integer'],
        ])->validate();

        $result = $this->orderHistoryService->bulk($validated['action'], $validated['history_ids']);
        $message = sprintf('%d history record(s) processed.', $result['processed']);

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