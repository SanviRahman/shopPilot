<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\MetaPixelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MetaPixelEventController extends Controller
{
    public function __invoke(Request $request, MetaPixelService $service): JsonResponse
    {
        if ($service->activePixelIds() === []) {
            return response()->json(['captured' => false, 'reason' => 'tracking_inactive']);
        }

        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z][A-Za-z0-9_]{0,79}$/'],
            'event_id' => ['nullable', 'uuid'],
            'page_url' => ['nullable', 'url', 'max:4000'],
            'referrer' => ['nullable', 'string', 'max:4000'],
            'payload' => ['nullable', 'array'],
            'delivery_status' => ['nullable', Rule::in(['captured', 'dispatched'])],
        ]);

        $service->captureEvent($request, $validated);
        return response()->json(['captured' => true], 201);
    }
}
