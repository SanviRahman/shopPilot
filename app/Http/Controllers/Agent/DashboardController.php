<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $title = 'Agent Dashboard';
        $breadcrumb = [
            ['text' => 'Dashboard', 'url' => null],
        ];

        return view('backoffice.agent.dashboard', compact('title', 'breadcrumb'));
    }
}