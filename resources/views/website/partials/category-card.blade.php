<a href="#featured" class="category-card">
    <div class="category-image-wrap">
        @if($category->image_url)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy">
        @else
            <img src="{{ asset('assets/website/images/category-placeholder.svg') }}" alt="{{ $category->name }}" loading="lazy">
        @endif
    </div>
    <strong>{{ $category->name }}</strong>
    <small>{{ (int) ($category->active_products_count ?? 0) }} products</small>
</a>
