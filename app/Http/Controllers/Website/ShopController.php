<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\ShopPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private readonly ShopPageService $shopPageService)
    {
    }

    public function index(Request $request): View
    {
        return view('website.shop.index', $this->shopPageService->getShopPageData($request));
    }
}
