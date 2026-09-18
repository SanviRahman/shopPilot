<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return response('ShopPilot Admin dashboard');
    }
}