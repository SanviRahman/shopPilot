@extends('website.layouts.app')

@section('title', 'ShopPilot Blog | Shopping Guides & Stories')
@section('meta_description', 'Read ShopPilot articles, product guides, shopping tips and ecommerce stories.')

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="content-page blog-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><strong>Blogs</strong></nav>

        <div class="blog-layout">
            <main class="blog-main">
                @if($featured)
                    <article class="blog-featured premium-panel" data-content-reveal>
                        <div class="blog-featured-copy"><span class="content-kicker"><i class="fas fa-star"></i> FEATURED ARTICLE</span><h1>{{ $featured->title }}</h1><p>{{ \Illuminate\Support\Str::limit(strip_tags($featured->content ?: 'Discover the latest ShopPilot ideas, product insights and shopping guidance.'), 180) }}</p><div class="blog-meta"><span><i class="far fa-calendar-alt"></i> {{ $featured->created_at?->format('M d, Y') }}</span><span><i class="far fa-user"></i> {{ $featured->author?->name ?? 'ShopPilot Team' }}</span></div><a href="{{ route('website.blogs.show', $featured->slug) }}" class="content-btn primary">Read Article <i class="fas fa-arrow-right"></i></a></div>
                        <div class="blog-featured-art" aria-hidden="true"><span class="blog-device"><i class="fas fa-laptop"></i></span><span class="blog-floating-icon icon-one"><i class="fas fa-lightbulb"></i></span><span class="blog-floating-icon icon-two"><i class="fas fa-shopping-cart"></i></span><span class="blog-floating-icon icon-three"><i class="fas fa-tags"></i></span></div>
                    </article>
                @else
                    <div class="premium-empty-state" data-content-reveal><span class="empty-visual"><i class="far fa-newspaper"></i></span><h2>No blog articles yet</h2><p>Publish a blog from the admin panel and it will automatically appear here.</p></div>
                @endif

                <section class="content-section blog-latest" data-content-reveal>
                    <div class="content-heading"><div><span>LATEST STORIES</span><h2>Latest Articles</h2><p>Shopping ideas, guides and product insights from ShopPilot.</p></div></div>
                    <div class="blog-topic-chips"><a href="{{ route('website.blogs.index') }}" class="{{ $topic === '' ? 'active' : '' }}">All Articles</a>@foreach($topics as $key => $definition)<a href="{{ route('website.blogs.index', ['topic' => $key]) }}" class="{{ $topic === $key ? 'active' : '' }}"><i class="fas {{ $definition['icon'] }}"></i>{{ $definition['label'] }}</a>@endforeach</div>
                    @if($blogs->isNotEmpty())
                        <div class="blog-grid">@foreach($blogs as $index => $blog)<article class="blog-card" data-content-reveal><a href="{{ route('website.blogs.show', $blog->slug) }}" class="blog-card-visual visual-{{ ($blog->id % 5) + 1 }}"><span><i class="fas {{ ['fa-headphones-alt','fa-shopping-bag','fa-mobile-alt','fa-home','fa-camera'][$blog->id % 5] }}"></i></span><small>ShopPilot Journal</small></a><div class="blog-card-body"><div class="blog-meta"><span><i class="far fa-calendar-alt"></i> {{ $blog->created_at?->format('M d, Y') }}</span><span><i class="far fa-user"></i> {{ $blog->author?->name ?? 'ShopPilot Team' }}</span></div><h3><a href="{{ route('website.blogs.show', $blog->slug) }}">{{ $blog->title }}</a></h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($blog->content ?: 'Read the latest ShopPilot shopping story and product insight.'), 115) }}</p><a href="{{ route('website.blogs.show', $blog->slug) }}" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a></div></article>@endforeach</div>
                        <div class="blog-pagination">{{ $blogs->onEachSide(1)->links('website.partials.pagination') }}</div>
                    @else
                        <div class="premium-empty-state compact" data-content-reveal><span class="empty-visual"><i class="fas fa-search"></i></span><h2>No articles matched</h2><p>Try another search keyword or topic.</p><a href="{{ route('website.blogs.index') }}" class="content-btn primary">Reset Blog Filters</a></div>
                    @endif
                </section>
            </main>

            <aside class="blog-sidebar" data-content-reveal>
                <section class="blog-side-card"><span class="side-card-kicker">SEARCH BLOG</span><form action="{{ route('website.blogs.index') }}" method="GET" class="blog-search"><input type="search" name="q" value="{{ $search }}" placeholder="Search articles, topics..."><button type="submit"><i class="fas fa-search"></i></button></form></section>
                <section class="blog-side-card"><span class="side-card-kicker">CATEGORIES</span><h2>Explore topics</h2><a href="{{ route('website.blogs.index') }}" class="blog-topic-link {{ $topic === '' ? 'active' : '' }}"><span><i class="fas fa-th-large"></i> All Articles</span><i class="fas fa-chevron-right"></i></a>@foreach($topics as $key => $definition)<a href="{{ route('website.blogs.index', ['topic' => $key]) }}" class="blog-topic-link {{ $topic === $key ? 'active' : '' }}"><span><i class="fas {{ $definition['icon'] }}"></i>{{ $definition['label'] }}</span><i class="fas fa-chevron-right"></i></a>@endforeach</section>
                @if($popular->isNotEmpty())<section class="blog-side-card"><span class="side-card-kicker">RECENT POSTS</span><h2>More to read</h2><div class="popular-posts">@foreach($popular as $post)<a href="{{ route('website.blogs.show', $post->slug) }}"><span class="popular-post-icon"><i class="far fa-newspaper"></i></span><div><strong>{{ \Illuminate\Support\Str::limit($post->title, 54) }}</strong><small>{{ $post->created_at?->format('M d, Y') }}</small></div></a>@endforeach</div></section>@endif
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
