<nav class="shop-breadcrumb" aria-label="Breadcrumb" data-shop-breadcrumb data-shop-reveal>
    <a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i><a href="{{ route('website.shop') }}" class="{{ $shopContext === 'shop' && $selectedCategories->isEmpty() && $searchTerm === '' ? 'current' : '' }}">Shop</a>
    @if($searchTerm !== '')<i class="fas fa-chevron-right"></i><strong>Search Results</strong>@endif
    @if($shopContext !== 'shop')<i class="fas fa-chevron-right"></i><strong>{{ $shopContextLabel }}</strong>@endif
    @if($selectedCategories->count() === 1)<i class="fas fa-chevron-right"></i><strong>{{ $selectedCategories->first()->name }}</strong>@endif
</nav>
