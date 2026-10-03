<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPageController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = $request->string('q')->trim()->toString();
        $topic = $request->string('topic')->trim()->toString();
        $query = Blog::query()->with('author')->latest('id');
        if ($search !== '') $query->where(fn (Builder $builder) => $builder->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%"));
        $this->applyTopic($query, $topic);
        $blogs = $query->paginate(8)->withQueryString();
        $data = ['blogs' => $blogs, 'featured' => Blog::query()->with('author')->latest('id')->first(), 'popular' => Blog::query()->latest('id')->limit(5)->get(), 'search' => $search, 'topic' => $topic, 'topics' => $this->topics()];
        if ($request->ajax() || $request->expectsJson()) return response()->json(['success' => true, 'html' => view('website.blog.partials.ajax-region', $data)->render(), 'url' => $request->fullUrl()]);
        return view('website.blog.index', $data);
    }

    public function show(string $slug): View
    {
        $blog = Blog::query()->with('author')->where('slug', $slug)->firstOrFail();
        $related = Blog::query()->where('id', '!=', $blog->getKey())->latest('id')->limit(3)->get();
        return view('website.blog.show', ['blog' => $blog, 'related' => $related, 'topic' => $this->resolveTopic($blog)]);
    }

    private function applyTopic(Builder $query, string $topic): void
    {
        $definition = $this->topics()[$topic] ?? null;
        if (! $definition) return;
        $query->where(function (Builder $builder) use ($definition) {
            foreach ($definition['keywords'] as $keyword) $builder->orWhere('title', 'like', "%{$keyword}%")->orWhere('description', 'like', "%{$keyword}%")->orWhere('content', 'like', "%{$keyword}%");
        });
    }

    private function topics(): array
    {
        return ['electronics' => ['label' => 'Electronics', 'icon' => 'fa-microchip', 'keywords' => ['phone', 'laptop', 'tech', 'electronic', 'watch', 'earbud', 'camera']], 'shopping-tips' => ['label' => 'Shopping Tips', 'icon' => 'fa-shopping-bag', 'keywords' => ['shopping', 'deal', 'save', 'buy', 'offer']], 'product-guides' => ['label' => 'Product Guides', 'icon' => 'fa-book-open', 'keywords' => ['guide', 'choose', 'review', 'comparison']], 'home-living' => ['label' => 'Home & Living', 'icon' => 'fa-home', 'keywords' => ['home', 'living', 'kitchen', 'appliance']], 'fashion' => ['label' => 'Fashion', 'icon' => 'fa-tshirt', 'keywords' => ['fashion', 'style', 'wear', 'clothing']]];
    }

    private function resolveTopic(Blog $blog): string
    {
        $haystack = Str::lower($blog->title.' '.$blog->description.' '.$blog->content);
        foreach ($this->topics() as $definition) foreach ($definition['keywords'] as $keyword) if (str_contains($haystack, Str::lower($keyword))) return $definition['label'];
        return 'ShopPilot Stories';
    }
}
