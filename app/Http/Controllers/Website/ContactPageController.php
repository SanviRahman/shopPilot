<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\StoreContactMessageRequest;
use App\Services\Website\ContactPageService;
use App\Services\Website\MapEmbedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactPageController extends Controller
{
    public function __construct(private readonly ContactPageService $contactPageService, private readonly MapEmbedService $mapEmbedService)
    {
    }

    public function index(): View
    {
        $contact = $this->contactPageService->primaryContact();
        return view('website.contact.index', ['contact' => $contact, 'mapEmbedUrl' => $this->mapEmbedService->embedUrl($contact?->map_url)]);
    }

    public function store(StoreContactMessageRequest $request): JsonResponse|RedirectResponse
    {
        $this->contactPageService->storeMessage($request->validated(), $request->ip(), $request->userAgent());
        $message = 'Thanks! Your message has been received. Our support team will contact you soon.';
        if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => true, 'message' => $message]);
        return redirect()->route('website.contact')->with('success', $message);
    }
}
