<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\TrackOrderRequest;
use App\Services\Website\TrackOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function __construct(private readonly TrackOrderService $trackOrderService)
    {
    }

    public function index(): View
    {
        return view('website.tracking.index');
    }

    public function lookup(TrackOrderRequest $request): JsonResponse|View
    {
        $order = $this->trackOrderService->find(
            $request->validated('order_number'),
            $request->validated('contact'),
        );

        $data = [
            'order' => $order,
            'timeline' => $this->trackOrderService->timeline($order),
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order tracking details loaded.',
                'html' => view('website.tracking.partials.result', $data)->render(),
            ]);
        }

        return view('website.tracking.index', $data);
    }
}
