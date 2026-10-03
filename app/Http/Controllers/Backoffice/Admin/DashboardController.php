<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(): View
    {
        if (! auth('admin')->user()?->can('dashboard.view')) {
            throw new AuthorizationException('You are not authorized to view the dashboard.');
        }

        $admin = auth('admin')->user();

        return view('backoffice.admin.dashboard', [
            'title' => $admin?->isRestrictedAgent() ? 'Agent Dashboard' : 'Admin Dashboard',
            'breadcrumb' => [['text' => 'Dashboard', 'url' => null]],
            ...$this->dashboardService->data($admin),
        ]);
    }
}
