<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('coupons.view');

        $filters = $request->only(['search', 'status']);
        $coupons = $this->couponService->getPaginatedCoupons($filters, true);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.coupons.partials.table', [
                    'coupons' => $coupons,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $coupons->hasPages() ? (string) $coupons->links() : '',
            ]);
        }

        return view('backoffice.admin.coupons.trash', [
            'title' => 'Coupon Trash Bin',
            'breadcrumb' => [
                ['text' => 'Coupons', 'url' => route('admin.coupons.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'coupons' => $coupons,
            'filters' => $filters,
        ]);
    }

    public function store(StoreCouponRequest $request): JsonResponse|RedirectResponse
    {
        $coupon = $this->couponService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully.',
                'coupon' => $coupon,
            ]);
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function show(Request $request, Coupon $coupon): View|JsonResponse
    {
        $this->authorizeAction('coupons.view');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => ucfirst($coupon->type),
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount ?? 'N/A',
                    'expires_at' => optional($coupon->expires_at)->format('d M Y, h:i A') ?? 'No Expiry',
                    'status' => ucfirst($coupon->status),
                    'created_at' => optional($coupon->created_at)->format('d M Y, h:i A'),
                    'updated_at' => optional($coupon->updated_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.coupons.show', compact('coupon'));
    }

    public function edit(Request $request, Coupon $coupon): JsonResponse|View
    {
        $this->authorizeAction('coupons.update');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'expires_at' => optional($coupon->expires_at)->format('Y-m-d\TH:i'),
                    'status' => $coupon->status,
                ],
            ]);
        }

        return $this->indexView($request, $coupon, true);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse|RedirectResponse
    {
        $this->couponService->update($coupon, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully.',
            ]);
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Request $request, Coupon $coupon): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('coupons.delete');
        $this->couponService->delete($coupon);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Coupon moved to trash successfully.');
    }

    public function restore(Request $request, int $coupon): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('coupons.restore');
        $this->couponService->restore($coupon);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon restored successfully.',
            ]);
        }

        return back()->with('success', 'Coupon restored successfully.');
    }

    public function forceDelete(Request $request, int $coupon): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('coupons.force-delete');
        $this->couponService->forceDelete($coupon);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon permanently deleted.',
            ]);
        }

        return back()->with('success', 'Coupon permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'coupons.delete',
            'restore' => 'coupons.restore',
            'force-delete' => 'coupons.force-delete',
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
            'coupon_ids' => ['required', 'array', 'min:1'],
            'coupon_ids.*' => ['integer'],
        ])->validate();

        $result = $this->couponService->bulk($validated['action'], $validated['coupon_ids']);
        $message = sprintf('%d coupon(s) processed.', $result['processed']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Coupon $formCoupon = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('coupons.view');

        $filters = $request->only(['search', 'status']);
        $coupons = $this->couponService->getPaginatedCoupons($filters, false);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.coupons.partials.table', [
                    'coupons' => $coupons,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $coupons->hasPages() ? (string) $coupons->links() : '',
            ]);
        }

        return view('backoffice.admin.coupons.index', [
            'title' => 'Coupon Management',
            'breadcrumb' => [
                ['text' => 'Coupons', 'url' => null],
            ],
            'coupons' => $coupons,
            'filters' => $filters,
            'formCoupon' => $formCoupon,
            'formMode' => $formCoupon ? 'edit' : 'create',
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