<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\CustomerPaymentSubmissionRequest;
use App\Services\Website\CustomerPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CustomerPaymentController extends Controller
{
    public function __construct(private readonly CustomerPaymentService $paymentService)
    {
    }

    public function store(CustomerPaymentSubmissionRequest $request): JsonResponse|RedirectResponse
    {
        $payment = $this->paymentService->submit(
            $request->user('web'),
            $request->validated(),
        );

        $message = "Payment submitted for {$payment->order->order_number}. ShopPilot staff will verify it shortly.";

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('website.account.payments'),
            ], 201);
        }

        return redirect()
            ->route('website.account.payments')
            ->with('success', $message);
    }
}
