@extends('website.layouts.app')

@section('title', $blog->title.' | ShopPilot Blog')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($blog->description ?: $blog->content ?: $blog->title), 155))

@push('styles')<link rel="stylesheet" href="{{ asset('assets/website/css/content-pages.css') }}">@endpush

@section('content')
<section class="content-page blog-detail-page">
    <div class="container">
        <nav class="content-breadcrumb" data-content-reveal><a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.blogs.index') }}">Blogs</a><i class="fas fa-chevron-right"></i><strong>{{ \Illuminate\Support\Str::limit($blog->title, 42) }}</strong></nav>
        <article class="blog-detail premium-panel" data-content-reveal><header><span class="content-kicker">{{ strtoupper($topic) }}</span><h1>{{ $blog->title }}</h1><div class="blog-meta"><span><i class="far fa-calendar-alt"></i> {{ $blog->created_at?->format('F d, Y') }}</span><span><i class="far fa-user"></i> {{ $blog->author?->name ?? 'ShopPilot Team' }}</span><span><i class="far fa-clock"></i> {{ max(1, (int) ceil(str_word_count(strip_tags($blog->content ?: '')) / 220)) }} min read</span></div>@if($blog->description)<p class="blog-detail-description">{{ $blog->description }}</p>@endif</header><div class="blog-detail-hero"><span><i class="far fa-newspaper"></i></span><div><small>SHOPPILOT JOURNAL</small><strong>Ideas for smarter shopping</strong></div></div><div class="blog-prose">{!! nl2br(e($blog->content ?: 'This article is ready for content from the ShopPilot admin panel.')) !!}</div><footer class="blog-detail-footer"><div><span>Share this story</span><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a><a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a></div><a href="{{ route('website.blogs.index') }}" class="content-btn secondary"><i class="fas fa-arrow-left"></i> Back to Blogs</a></footer></article>
        @if($related->isNotEmpty())<section class="content-section" data-content-reveal><div class="content-heading"><div><span>KEEP READING</span><h2>Related Articles</h2></div><a href="{{ route('website.blogs.index') }}" class="content-link">View All <i class="fas fa-arrow-right"></i></a></div><div class="blog-grid related">@foreach($related as $item)<article class="blog-card"><div class="blog-card-body"><div class="blog-meta"><span><i class="far fa-calendar-alt"></i> {{ $item->created_at?->format('M d, Y') }}</span></div><h3><a href="{{ route('website.blogs.show', $item->slug) }}">{{ $item->title }}</a></h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($item->description ?: $item->content ?: ''), 110) }}</p><a href="{{ route('website.blogs.show', $item->slug) }}" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a></div></article>@endforeach</div></section>@endif
    </div>
</section>
@endsection

@push('scripts')<script src="{{ asset('assets/website/js/content-pages.js') }}" defer></script>@endpush
