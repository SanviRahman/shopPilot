<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAction('media.view');

        return view('backoffice.admin.media.index', [
            'title' => 'Media Management',
            'breadcrumb' => [
                ['text' => 'Media', 'url' => null],
            ],
            'media' => $this->mediaQuery($request)
                ->paginate(24)
                ->withQueryString(),
            'stats' => $this->stats(),
            'filters' => $request->only([
                'search',
                'type',
                'collection',
                'disk',
            ]),
            'isTrash' => false,
        ]);
    }

    public function trash(Request $request): View
    {
        $this->authorizeAction('media.view');

        return view('backoffice.admin.media.trash', [
            'title' => 'Media Trash',
            'breadcrumb' => [
                [
                    'text' => 'Media',
                    'url' => route('admin.media.index'),
                ],
                [
                    'text' => 'Trash',
                    'url' => null,
                ],
            ],
            'media' => $this->mediaQuery($request, true)
                ->paginate(24)
                ->withQueryString(),
            'filters' => $request->only([
                'search',
                'type',
                'collection',
                'disk',
            ]),
            'isTrash' => true,
        ]);
    }

    public function picker(Request $request): JsonResponse
    {
        $this->authorizeAction('media.view');

        $items = $this->mediaQuery($request)
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->paginate(24);

        return response()->json([
            'data' => $items
                ->getCollection()
                ->map(fn (Media $media) => $this->pickerPayload($media))
                ->values()
                ->all(),

            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function destroy(Request $request, int $media): RedirectResponse
    {
        $this->authorizeAction('media.delete');

        Media::query()
            ->findOrFail($media)
            ->delete();

        return back()->with(
            'success',
            'Media moved to trash successfully.'
        );
    }

    public function restore(Request $request, int $media): RedirectResponse
    {
        $this->authorizeAction('media.restore');

        Media::onlyTrashed()
            ->findOrFail($media)
            ->restore();

        return back()->with(
            'success',
            'Media restored successfully.'
        );
    }

    public function forceDelete(
        Request $request,
        int $media
    ): RedirectResponse {
        $this->authorizeAction('media.force-delete');

        Media::onlyTrashed()
            ->findOrFail($media)
            ->forceDelete();

        return back()->with(
            'success',
            'Media permanently deleted.'
        );
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => [
                'required',
                'in:delete,restore,force-delete',
            ],
            'media_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'media_ids.*' => [
                'integer',
                'distinct',
                'exists:media,id',
            ],
        ]);

        $permission = match ($validated['action']) {
            'delete' => 'media.delete',
            'restore' => 'media.restore',
            'force-delete' => 'media.force-delete',
        };

        $this->authorizeAction($permission);

        $processed = 0;

        $mediaIds = collect($validated['media_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($mediaIds as $id) {
            $media = match ($validated['action']) {
                'delete' => Media::query()->find($id),
                'restore',
                'force-delete' => Media::onlyTrashed()->find($id),
            };

            if (! $media) {
                continue;
            }

            match ($validated['action']) {
                'delete' => $media->delete(),
                'restore' => $media->restore(),
                'force-delete' => $media->forceDelete(),
            };

            $processed++;
        }

        return back()->with(
            'success',
            sprintf('%d media file(s) processed.', $processed)
        );
    }

    private function mediaQuery(
        Request $request,
        bool $trash = false
    ) {
        return ($trash ? Media::onlyTrashed() : Media::query())
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $term = '%'
                        . $request->string('search')->trim()
                        . '%';

                    $query->where(function ($nested) use ($term): void {
                        $nested
                            ->where('name', 'like', $term)
                            ->orWhere('file_name', 'like', $term)
                            ->orWhere(
                                'collection_name',
                                'like',
                                $term
                            )
                            ->orWhere(
                                'model_type',
                                'like',
                                $term
                            );
                    });
                }
            )
            ->when(
                $request->filled('type'),
                function ($query) use ($request): void {
                    $type = $request
                        ->string('type')
                        ->toString();

                    $query
                        ->when(
                            $type === 'image',
                            fn ($q) => $q->where(
                                'mime_type',
                                'like',
                                'image/%'
                            )
                        )
                        ->when(
                            $type === 'video',
                            fn ($q) => $q->where(
                                'mime_type',
                                'like',
                                'video/%'
                            )
                        )
                        ->when(
                            $type === 'other',
                            fn ($q) => $q->whereNot(
                                function ($q) {
                                    $q
                                        ->where(
                                            'mime_type',
                                            'like',
                                            'image/%'
                                        )
                                        ->orWhere(
                                            'mime_type',
                                            'like',
                                            'video/%'
                                        );
                                }
                            )
                        );
                }
            )
            ->when(
                $request->filled('collection'),
                fn ($query) => $query->where(
                    'collection_name',
                    $request->string('collection')->toString()
                )
            )
            ->when(
                $request->filled('disk'),
                fn ($query) => $query->where(
                    'disk',
                    $request->string('disk')->toString()
                )
            )
            ->latest('id');
    }

    private function stats(): array
    {
        $active = Media::query();

        return [
            'total' => (clone $active)->count(),
            'images' => (clone $active)
                ->where('mime_type', 'like', 'image/%')
                ->count(),
            'videos' => (clone $active)
                ->where('mime_type', 'like', 'video/%')
                ->count(),
            'storage' => (clone $active)->sum('size'),
        ];
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException(
                'You are not authorized to perform this action.'
            );
        }
    }

    private function pickerPayload(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => $media->getUrl(),
            'thumb_url' => $media->getUrl(),
            'collection_name' => $media->collection_name,
            'created_at' => optional($media->created_at)
                ->format('d M Y, h:i A'),
        ];
    }
}