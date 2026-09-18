<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $title = 'Admin Dashboard';
        $breadcrumb = [
            ['text' => 'Dashboard', 'url' => null],
        ];

        return view('backoffice.admin.dashboard', compact('title', 'breadcrumb'));
    }
}