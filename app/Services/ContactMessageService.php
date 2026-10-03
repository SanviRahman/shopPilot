<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContactMessageService
{
    public function paginate(array $filters = [], bool $trash = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = $trash ? ContactMessage::onlyTrashed() : ContactMessage::query();
        return $query->when(! empty($filters['search']), function ($query) use ($filters) {
            $search = trim((string) $filters['search']);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%");
            });
        })->when(! empty($filters['status']) && ! $trash, fn ($query) => $query->where('status', $filters['status']))->latest($trash ? 'deleted_at' : 'id')->paginate($perPage)->withQueryString();
    }

    public function updateStatus(ContactMessage $message, string $status): ContactMessage
    {
        return DB::transaction(function () use ($message, $status): ContactMessage {
            $message->status = $status;
            if ($status === 'read' && ! $message->read_at) $message->read_at = now();
            if ($status === 'resolved') {
                if (! $message->read_at) $message->read_at = now();
                $message->resolved_at = now();
            }
            if ($status === 'new') {
                $message->read_at = null;
                $message->resolved_at = null;
            }
            if ($status === 'read') $message->resolved_at = null;
            $message->save();
            return $message->refresh();
        });
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }

    public function restore(ContactMessage $message): void
    {
        $message->restore();
    }

    public function forceDelete(ContactMessage $message): void
    {
        $message->forceDelete();
    }

    public function bulkAction(string $action, array $ids): int
    {
        $processed = 0;
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            $message = in_array($action, ['restore', 'force-delete'], true) ? ContactMessage::onlyTrashed()->find($id) : ContactMessage::find($id);
            if (! $message) continue;
            match ($action) {
                'mark-read' => $this->updateStatus($message, 'read'),
                'mark-resolved' => $this->updateStatus($message, 'resolved'),
                'delete' => $this->delete($message),
                'restore' => $this->restore($message),
                'force-delete' => $this->forceDelete($message),
            };
            $processed++;
        }
        return $processed;
    }
}
