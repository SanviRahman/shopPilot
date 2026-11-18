<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorService
{
    public function submitApplication(User $user, array $data): Vendor
    {
        return DB::transaction(function () use ($user, $data): Vendor {
            $vendor = Vendor::create([
                'business_name' => $data['business_name'],
                'legal_name' => $data['legal_name'] ?? null,
                'business_type' => $data['business_type'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'website' => $data['website'] ?? null,
                'tax_identifier' => $data['tax_identifier'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'status' => Vendor::STATUS_PENDING_REVIEW,
            ]);

            $baseSlug = Str::slug(($data['store_slug'] ?? null) ?: $data['store_name']);
            $baseSlug = $baseSlug !== '' ? $baseSlug : 'store';
            $slug = $baseSlug;

            while (Store::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.Str::lower(Str::random(5));
            }

            $vendor->store()->create([
                'name' => $data['store_name'],
                'slug' => $slug,
                'description' => $data['store_description'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'status' => 'inactive',
            ]);

            $vendor->users()->attach($user->getKey(), [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            foreach ($data['documents'] as $document) {
                if (! $document instanceof UploadedFile) {
                    continue;
                }

                $path = Storage::disk('local')->putFile('vendor-documents/'.$vendor->getKey(), $document);

                if (! $path) {
                    throw ValidationException::withMessages([
                        'documents' => 'A verification document could not be stored. Please try again.',
                    ]);
                }

                $vendor->documents()->create([
                    'uploaded_by_user_id' => $user->getKey(),
                    'document_type' => $data['document_type'],
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $document->getClientOriginalName(),
                    'mime_type' => $document->getMimeType(),
                    'file_size' => $document->getSize(),
                    'status' => 'pending',
                ]);
            }

            $vendor->statusHistories()->create([
                'changed_by_user_id' => $user->getKey(),
                'from_status' => null,
                'to_status' => Vendor::STATUS_PENDING_REVIEW,
                'reason' => 'Seller application submitted.',
                'created_at' => now(),
            ]);

            return $vendor->load(['store', 'documents']);
        });
    }

    public function approve(Vendor $vendor, Admin $admin): Vendor
    {
        return $this->transition($vendor, Vendor::STATUS_APPROVED, $admin);
    }

    public function reject(Vendor $vendor, Admin $admin, string $reason): Vendor
    {
        return $this->transition($vendor, Vendor::STATUS_REJECTED, $admin, $reason);
    }

    public function suspend(Vendor $vendor, Admin $admin, string $reason): Vendor
    {
        return $this->transition($vendor, Vendor::STATUS_SUSPENDED, $admin, $reason);
    }

    public function reactivate(Vendor $vendor, Admin $admin): Vendor
    {
        return $this->transition($vendor, Vendor::STATUS_APPROVED, $admin, 'Vendor reactivated.');
    }

    private function transition(Vendor $vendor, string $status, Admin $admin, ?string $reason = null): Vendor
    {
        return DB::transaction(function () use ($vendor, $status, $admin, $reason): Vendor {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($vendor->getKey());

            if (! $vendor->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => "Vendor cannot transition from {$vendor->status} to {$status}.",
                ]);
            }

            $fromStatus = $vendor->status;
            $vendor->status = $status;
            $vendor->reviewed_by_admin_id = $admin->getKey();
            $vendor->reviewed_at = now();

            if ($status === Vendor::STATUS_APPROVED) {
                $vendor->approved_at = now();
                $vendor->suspended_at = null;
            }

            if ($status === Vendor::STATUS_SUSPENDED) {
                $vendor->suspended_at = now();
            }

            $vendor->save();
            $vendor->store()->update([
                'status' => $status === Vendor::STATUS_APPROVED ? 'active' : 'inactive',
            ]);

            $vendor->statusHistories()->create([
                'changed_by_admin_id' => $admin->getKey(),
                'from_status' => $fromStatus,
                'to_status' => $status,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            return $vendor->refresh();
        });
    }
}
