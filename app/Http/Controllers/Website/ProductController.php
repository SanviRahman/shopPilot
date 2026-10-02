<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\ProductDetailsService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductDetailsService $productDetailsService)
    {
    }

    public function show(string $slug): View
    {
        return view(
            'website.products.show',
            $this->productDetailsService->getProductDetailsData($slug),
        );
    }
}
