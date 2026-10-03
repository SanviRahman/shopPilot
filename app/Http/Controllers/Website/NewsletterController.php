<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    public function store(StoreNewsletterSubscriptionRequest $request): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request): void { $email = $request->validated('email'); $subscriber = NewsletterSubscriber::withTrashed()->where('email', $email)->first() ?: new NewsletterSubscriber(['email' => $email]); $subscriber->status = 'subscribed'; $subscriber->subscribed_at = now(); $subscriber->unsubscribed_at = null; $subscriber->save(); if ($subscriber->trashed()) $subscriber->restore(); });
        $message = 'Thanks! You are subscribed to ShopPilot updates.';
        if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => true, 'message' => $message]);
        return back()->with('success', $message);
    }
}
