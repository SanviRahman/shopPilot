<?php

namespace App\Services\Vendor;

use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VendorStoreService
{
    public function update(Store $store, array $data): Store
    {
        return DB::transaction(function () use ($store, $data): Store {
            $store->update([
                ...Arr::except($data, ['store_logo', 'store_banner']),
                'vacation_mode' => (bool) ($data['vacation_mode'] ?? false),
            ]);

            foreach (['store_logo', 'store_banner'] as $collection) {
                if (($data[$collection] ?? null) instanceof UploadedFile) {
                    $store->addMedia($data[$collection])->toMediaCollection($collection);
                }
            }

            return $store->refresh();
        });
    }
}
