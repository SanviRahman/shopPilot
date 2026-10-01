<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\HomePageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly HomePageService $homePageService)
    {
    }

    public function index(Request $request): View
    {
        $searchTerm = $request->string('q')->trim()->toString();

        return view('website.home.index', [
            ...$this->homePageService->getHomePageData($searchTerm),
            'searchTerm' => $searchTerm,
        ]);
    }
}
