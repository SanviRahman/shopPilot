<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VendorOrder;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerRefundController extends Controller
{
    public function __construct(private readonly MarketplaceOrderService $orderService)
    {
    }

    public function store(Request $request, string $orderNumber, VendorOrder $vendorOrder): RedirectResponse
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user('web')->id)
            ->firstOrFail();

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->orderService->createRefundRequest(
            $order,
            $vendorOrder,
            $data['items'],
            $data['reason'],
            (int) $request->user('web')->id,
        );

        return back()->with('success', 'Your refund request has been submitted for review.');
    }
}
