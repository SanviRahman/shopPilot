<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return response('ShopPilot Agent dashboard');
    }
}