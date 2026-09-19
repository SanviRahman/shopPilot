<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAvatarService
{
    public function syncFromRequest(Request $request, Admin $admin): void
    {
        /*
        |--------------------------------------------------------------------------
        | New Image Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            $admin
                ->addMedia($file)
                ->usingFileName(
                    $this->safeFilename(
                        $file->getClientOriginalExtension()
                    )
                )
                ->toMediaCollection('avatars', 'public');

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Media Selection
        |--------------------------------------------------------------------------
        */

        if ($request->filled('photo_media_id')) {
            $media = Media::query()
                ->whereKey((int) $request->input('photo_media_id'))
                ->where('mime_type', 'like', 'image/%')
                ->first();

            if (! $media) {
                throw ValidationException::withMessages([
                    'photo_media_id' => 'The selected media image is not available.',
                ]);
            }

            $extension = pathinfo(
                $media->file_name,
                PATHINFO_EXTENSION
            );

            $admin
                ->addMedia($media->getPath())
                ->preservingOriginal()
                ->usingName($media->name)
                ->usingFileName(
                    $this->safeFilename($extension)
                )
                ->toMediaCollection('avatars', 'public');
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));

        $extension = preg_replace(
            '/[^a-z0-9]+/',
            '',
            $extension
        ) ?: 'jpg';

        return uniqid('avatar_', true) . '.' . $extension;
    }
}