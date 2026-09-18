<?php

namespace App\Http\Controllers\Backoffice\Manager;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return response('ShopPilot Manager dashboard');
    }
}