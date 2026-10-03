<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetaPixel\StoreMetaPixelRequest;
use App\Http\Requests\MetaPixel\UpdateMetaPixelRequest;
use App\Models\MetaPixel;
use App\Models\MetaPixelEvent;
use App\Services\MetaPixelService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetaPixelController extends Controller
{
    public function __construct(private readonly MetaPixelService $service)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAction('meta-pixels.view');

        $pixels = MetaPixel::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%");
            })
            ->when(
                $request->filled('lifecycle_status'),
                fn ($query) => $query->where('lifecycle_status', $request->string('lifecycle_status')->toString())
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return $this->tableResponse($pixels, false);
        }

        $events = MetaPixelEvent::query()->latest('occurred_at')->limit(20)->get();
        $eventCounts = MetaPixelEvent::query()
            ->selectRaw('event_name, COUNT(*) AS total')
            ->groupBy('event_name')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'event_name');

        return view('backoffice.admin.meta-pixels.index', [
            'title' => 'Meta Pixel Tracking',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => null]],
            'pixels' => $pixels,
            'events' => $events,
            'eventCounts' => $eventCounts,
        ]);
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.create');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'pixel' => $this->emptyPixelPayload(),
            ]);
        }

        return redirect()->route('admin.meta-pixels.index');
    }

    public function store(StoreMetaPixelRequest $request): JsonResponse|RedirectResponse
    {
        $pixel = $this->service->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meta Pixel configuration created successfully.',
                'pixel' => $this->pixelPayload($pixel),
            ]);
        }

        return redirect()->route('admin.meta-pixels.index')->with('success', 'Meta Pixel configuration created.');
    }

    public function show(Request $request, MetaPixel $metaPixel): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.view');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'pixel' => $this->pixelPayload($metaPixel),
                'events' => $this->recentEventsFor($metaPixel),
            ]);
        }

        return redirect()->route('admin.meta-pixels.index', ['show' => $metaPixel->getKey()]);
    }

    public function edit(Request $request, MetaPixel $metaPixel): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.update');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'pixel' => $this->pixelPayload($metaPixel),
            ]);
        }

        return redirect()->route('admin.meta-pixels.index', ['edit' => $metaPixel->getKey()]);
    }

    public function update(UpdateMetaPixelRequest $request, MetaPixel $metaPixel): JsonResponse|RedirectResponse
    {
        $pixel = $this->service->update($metaPixel, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meta Pixel configuration updated successfully.',
                'pixel' => $this->pixelPayload($pixel),
            ]);
        }

        return redirect()->route('admin.meta-pixels.index')->with('success', 'Meta Pixel configuration updated.');
    }

    public function destroy(Request $request, MetaPixel $metaPixel): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.delete');
        $this->service->delete($metaPixel);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meta Pixel moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Meta Pixel moved to trash.');
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('meta-pixels.view');

        $pixels = MetaPixel::onlyTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return $this->tableResponse($pixels, true);
        }

        return view('backoffice.admin.meta-pixels.trash', [
            'title' => 'Meta Pixel Trash',
            'breadcrumb' => [
                ['text' => 'Meta Pixel', 'url' => route('admin.meta-pixels.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'pixels' => $pixels,
        ]);
    }

    public function restore(Request $request, int $metaPixel): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.restore');
        $this->service->restore(MetaPixel::onlyTrashed()->findOrFail($metaPixel));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meta Pixel restored successfully.',
            ]);
        }

        return back()->with('success', 'Meta Pixel restored.');
    }

    public function forceDelete(Request $request, int $metaPixel): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('meta-pixels.force-delete');
        $this->service->forceDelete(MetaPixel::onlyTrashed()->findOrFail($metaPixel));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meta Pixel permanently deleted.',
            ]);
        }

        return back()->with('success', 'Meta Pixel permanently deleted.');
    }

    private function tableResponse($pixels, bool $isTrash): JsonResponse
    {
        return response()->json([
            'success' => true,
            'html' => view('backoffice.admin.meta-pixels.partials.table', [
                'pixels' => $pixels,
                'isTrash' => $isTrash,
            ])->render(),
            'pagination' => $pixels->hasPages() ? (string) $pixels->links() : '',
        ]);
    }

    /** @return array<string, mixed> */
    private function emptyPixelPayload(): array
    {
        return [
            'id' => null,
            'name' => '',
            'pixel_entries' => [['pixel_id' => '', 'script' => '']],
            'lifecycle_status' => 'draft',
            'track_page_view' => true,
            'track_ecommerce' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function pixelPayload(MetaPixel $pixel): array
    {
        return [
            'id' => $pixel->getKey(),
            'name' => $pixel->name,
            'pixel_ids' => array_values((array) $pixel->pixel_ids),
            'pixel_entries' => $pixel->pixelEntries(),
            'lifecycle_status' => $pixel->lifecycle_status,
            'track_page_view' => (bool) $pixel->track_page_view,
            'track_ecommerce' => (bool) $pixel->track_ecommerce,
            'starts_at' => $pixel->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $pixel->ends_at?->format('Y-m-d\TH:i'),
            'created_at' => $pixel->created_at?->format('d M Y, h:i A'),
            'updated_at' => $pixel->updated_at?->format('d M Y, h:i A'),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function recentEventsFor(MetaPixel $pixel): array
    {
        $pixelIds = collect($pixel->pixel_ids)->filter()->map(fn ($id) => (string) $id)->values();

        if ($pixelIds->isEmpty()) {
            return [];
        }

        return MetaPixelEvent::query()
            ->where(function ($query) use ($pixelIds) {
                foreach ($pixelIds as $pixelId) {
                    $query->orWhereJsonContains('pixel_ids', $pixelId);
                }
            })
            ->latest('occurred_at')
            ->limit(20)
            ->get()
            ->map(fn (MetaPixelEvent $event) => [
                'event_name' => $event->event_name,
                'page_url' => $event->page_url,
                'delivery_status' => $event->delivery_status,
                'occurred_at' => $event->occurred_at?->format('d M Y, h:i:s A'),
            ])
            ->values()
            ->all();
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
