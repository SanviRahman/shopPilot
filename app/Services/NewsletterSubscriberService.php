<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NewsletterSubscriberService
{
    public function paginate(array $filters = [], bool $trash = false, int $perPage = 20): LengthAwarePaginator
    {
        $query = $trash ? NewsletterSubscriber::onlyTrashed() : NewsletterSubscriber::query();
        return $query->when(! empty($filters['search']), function ($query) use ($filters) { $search = trim((string) $filters['search']); $query->where('email', 'like', "%{$search}%"); })->when(! empty($filters['status']) && ! $trash, fn ($query) => $query->where('status', $filters['status']))->latest($trash ? 'deleted_at' : 'id')->paginate($perPage)->withQueryString();
    }

    public function stats(): array
    {
        return ['total' => NewsletterSubscriber::query()->count(), 'subscribed' => NewsletterSubscriber::query()->where('status', 'subscribed')->count(), 'unsubscribed' => NewsletterSubscriber::query()->where('status', 'unsubscribed')->count(), 'trash' => NewsletterSubscriber::onlyTrashed()->count()];
    }

    public function create(array $data): NewsletterSubscriber
    {
        return DB::transaction(function () use ($data): NewsletterSubscriber { $subscriber = NewsletterSubscriber::withTrashed()->where('email', $data['email'])->first(); if ($subscriber && ! $subscriber->trashed()) throw ValidationException::withMessages(['email' => 'This email is already in the newsletter list.']); if (! $subscriber) $subscriber = new NewsletterSubscriber(); $subscriber->email = $data['email']; $this->applyStatus($subscriber, $data['status']); $subscriber->save(); if ($subscriber->trashed()) $subscriber->restore(); return $subscriber->refresh(); });
    }

    public function update(NewsletterSubscriber $subscriber, array $data): NewsletterSubscriber
    {
        return DB::transaction(function () use ($subscriber, $data): NewsletterSubscriber { $duplicate = NewsletterSubscriber::withTrashed()->where('email', $data['email'])->where('id', '!=', $subscriber->getKey())->exists(); if ($duplicate) throw ValidationException::withMessages(['email' => 'This email is already in the newsletter list.']); $subscriber->email = $data['email']; $this->applyStatus($subscriber, $data['status']); $subscriber->save(); return $subscriber->refresh(); });
    }

    public function changeStatus(NewsletterSubscriber $subscriber, string $status): NewsletterSubscriber
    {
        $this->applyStatus($subscriber, $status); $subscriber->save(); return $subscriber->refresh();
    }

    public function delete(NewsletterSubscriber $subscriber): void
    {
        $subscriber->delete();
    }

    public function restore(NewsletterSubscriber $subscriber): void
    {
        $subscriber->restore();
    }

    public function forceDelete(NewsletterSubscriber $subscriber): void
    {
        $subscriber->forceDelete();
    }

    public function bulkAction(string $action, array $ids): int
    {
        $processed = 0;
        foreach (array_unique(array_map('intval', $ids)) as $id) { $subscriber = in_array($action, ['restore', 'force-delete'], true) ? NewsletterSubscriber::onlyTrashed()->find($id) : NewsletterSubscriber::find($id); if (! $subscriber) continue; match ($action) { 'subscribe' => $this->changeStatus($subscriber, 'subscribed'), 'unsubscribe' => $this->changeStatus($subscriber, 'unsubscribed'), 'delete' => $this->delete($subscriber), 'restore' => $this->restore($subscriber), 'force-delete' => $this->forceDelete($subscriber) }; $processed++; }
        return $processed;
    }

    private function applyStatus(NewsletterSubscriber $subscriber, string $status): void
    {
        $subscriber->status = $status;
        if ($status === 'subscribed') { $subscriber->subscribed_at = now(); $subscriber->unsubscribed_at = null; return; }
        $subscriber->unsubscribed_at = now();
    }
}
