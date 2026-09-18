<?php

namespace App\Http\Controllers\Backoffice\Manager;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $title = 'Manager Dashboard';
        $breadcrumb = [
            ['text' => 'Dashboard', 'url' => null],
        ];

        return view('backoffice.manager.dashboard', compact('title', 'breadcrumb'));
    }
}