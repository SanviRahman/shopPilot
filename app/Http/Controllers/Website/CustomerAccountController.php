<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\CustomerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function __construct(private readonly CustomerAccountService $accountService)
    {
    }

    public function dashboard(Request $request): View
    {
        return view('website.account.dashboard', [
            ...$this->accountService->dashboardData($request->user('web')),
            'user' => $request->user('web'),
        ]);
    }

    public function orders(Request $request): View|JsonResponse
    {
        $data = [
            ...$this->accountService->ordersData(
                $request->user('web'),
                $request->string('status')->toString(),
                $request->string('q')->toString(),
            ),
            'user' => $request->user('web'),
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('website.account.orders.partials.ajax-region', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('website.account.orders.index', $data);
    }

    public function order(Request $request, string $orderNumber): View
    {
        return view('website.account.orders.show', [
            'user' => $request->user('web'),
            'order' => $this->accountService->ownedOrder($request->user('web'), $orderNumber),
        ]);
    }

    public function payments(Request $request): View|JsonResponse
    {
        $data = [
            ...$this->accountService->paymentData(
                $request->user('web'),
                $request->string('status')->toString(),
            ),
            'user' => $request->user('web'),
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('website.account.payments.partials.ajax-region', $data)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('website.account.payments.index', $data);
    }
}
