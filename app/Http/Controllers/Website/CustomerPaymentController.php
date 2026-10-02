<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\CustomerPaymentSubmissionRequest;
use App\Services\Website\CustomerPaymentService;
use Illuminate\Http\RedirectResponse;

class CustomerPaymentController extends Controller
{
    public function __construct(private readonly CustomerPaymentService $paymentService)
    {
    }

    public function store(CustomerPaymentSubmissionRequest $request): RedirectResponse
    {
        $payment = $this->paymentService->submit(
            $request->user('web'),
            $request->validated(),
        );

        return redirect()
            ->route('website.account.payments')
            ->with('success', "Payment submitted for {$payment->order->order_number}. ShopPilot staff will verify it shortly.");
    }
}
