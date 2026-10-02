<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetaPixel\StoreMetaPixelRequest;
use App\Http\Requests\MetaPixel\UpdateMetaPixelRequest;
use App\Models\MetaPixel;
use App\Models\MetaPixelEvent;
use App\Services\MetaPixelService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetaPixelController extends Controller
{
    public function __construct(private readonly MetaPixelService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorizeAction('meta-pixels.view');

        $pixels = MetaPixel::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search')->trim() . '%'))
            ->when($request->filled('lifecycle_status'), fn ($q) => $q->where('lifecycle_status', $request->string('lifecycle_status')))
            ->latest()->paginate(15)->withQueryString();

        $events = MetaPixelEvent::query()->latest('occurred_at')->limit(20)->get();
        $eventCounts = MetaPixelEvent::query()->selectRaw('event_name, COUNT(*) AS total')->groupBy('event_name')->orderByDesc('total')->limit(10)->pluck('total', 'event_name');

        return view('backoffice.admin.meta-pixels.index', [
            'title' => 'Meta Pixel Tracking',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => null]],
            'pixels' => $pixels,
            'events' => $events,
            'eventCounts' => $eventCounts,
        ]);
    }

    public function create(): View
    {
        $this->authorizeAction('meta-pixels.create');
        return view('backoffice.admin.meta-pixels.form', [
            'title' => 'Add Meta Pixel',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => route('admin.meta-pixels.index')], ['text' => 'Create', 'url' => null]],
            'pixel' => new MetaPixel(['lifecycle_status' => 'draft', 'track_page_view' => true, 'track_ecommerce' => true]),
        ]);
    }

    public function store(StoreMetaPixelRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('admin.meta-pixels.index')->with('success', 'Meta Pixel configuration created.');
    }

    public function show(MetaPixel $metaPixel): View
    {
        $this->authorizeAction('meta-pixels.view');
        return view('backoffice.admin.meta-pixels.show', [
            'title' => 'Meta Pixel Details',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => route('admin.meta-pixels.index')], ['text' => $metaPixel->name, 'url' => null]],
            'pixel' => $metaPixel,
            'events' => MetaPixelEvent::query()->whereJsonContains('pixel_ids', (string) (($metaPixel->pixel_ids ?? [])[0] ?? ''))->latest('occurred_at')->limit(50)->get(),
        ]);
    }

    public function edit(MetaPixel $metaPixel): View
    {
        $this->authorizeAction('meta-pixels.update');
        return view('backoffice.admin.meta-pixels.form', [
            'title' => 'Edit Meta Pixel',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => route('admin.meta-pixels.index')], ['text' => 'Edit', 'url' => null]],
            'pixel' => $metaPixel,
        ]);
    }

    public function update(UpdateMetaPixelRequest $request, MetaPixel $metaPixel): RedirectResponse
    {
        $this->service->update($metaPixel, $request->validated());
        return redirect()->route('admin.meta-pixels.index')->with('success', 'Meta Pixel configuration updated.');
    }

    public function destroy(MetaPixel $metaPixel): RedirectResponse
    {
        $this->authorizeAction('meta-pixels.delete');
        $this->service->delete($metaPixel);
        return back()->with('success', 'Meta Pixel moved to trash.');
    }

    public function trash(): View
    {
        $this->authorizeAction('meta-pixels.view');
        return view('backoffice.admin.meta-pixels.trash', [
            'title' => 'Meta Pixel Trash',
            'breadcrumb' => [['text' => 'Meta Pixel', 'url' => route('admin.meta-pixels.index')], ['text' => 'Trash', 'url' => null]],
            'pixels' => MetaPixel::onlyTrashed()->latest('deleted_at')->paginate(15),
        ]);
    }

    public function restore(int $metaPixel): RedirectResponse
    {
        $this->authorizeAction('meta-pixels.restore');
        $this->service->restore(MetaPixel::onlyTrashed()->findOrFail($metaPixel));
        return back()->with('success', 'Meta Pixel restored.');
    }

    public function forceDelete(int $metaPixel): RedirectResponse
    {
        $this->authorizeAction('meta-pixels.force-delete');
        $this->service->forceDelete(MetaPixel::onlyTrashed()->findOrFail($metaPixel));
        return back()->with('success', 'Meta Pixel permanently deleted.');
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
