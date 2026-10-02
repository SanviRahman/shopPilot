<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\ShopPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private readonly ShopPageService $shopPageService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $data = $this->shopPageService->getShopPageData($request);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('website.shop.partials.ajax-region', $data)->render(),
                'total_count' => (int) $data['products']->total(),
                'search_query' => (string) $data['searchTerm'],
                'url' => $request->fullUrl(),
            ]);
        }

        return view('website.shop.index', $data);
    }
}
