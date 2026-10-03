        <div data-shop-ajax-region>
        <div class="shop-mobile-toolbar" data-shop-reveal>
            <button type="button" class="mobile-filter-button" data-shop-filter-open aria-expanded="false" aria-controls="shop-filter-panel">
                <i class="fas fa-sliders-h"></i>
                Filters
                @if($activeFilterCount > 0)<span>{{ $activeFilterCount }}</span>@endif
            </button>
            <label class="mobile-sort-control">
                <span>Sort:</span>
                <select data-shop-sort-select>
                    <option value="featured" @selected($sort === 'featured')>Featured</option>
                    <option value="best_selling" @selected($sort === 'best_selling')>Best Selling</option>
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="price_low" @selected($sort === 'price_low')>Price: Low to High</option>
                    <option value="price_high" @selected($sort === 'price_high')>Price: High to Low</option>
                    <option value="name_az" @selected($sort === 'name_az')>Name: A-Z</option>
                </select>
            </label>
        </div>

        <div class="shop-layout">
            <div class="shop-filter-overlay" data-shop-filter-overlay></div>

            <aside id="shop-filter-panel" class="shop-sidebar" data-shop-filter-panel aria-hidden="true">
                <div class="filter-panel-head">
                    <div>
                        <span class="filter-icon"><i class="fas fa-sliders-h"></i></span>
                        <h2>Filters</h2>
                    </div>
                    <a href="{{ route('website.shop') }}">Clear All</a>
                    <button type="button" class="filter-close" data-shop-filter-close aria-label="Close filters"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('website.shop') }}" method="GET" class="shop-filter-form" data-shop-filter-form>
                    @if($searchTerm !== '')
                        <input type="hidden" name="q" value="{{ $searchTerm }}">
                    @endif
                    <input type="hidden" name="sort" value="{{ $sort }}" data-filter-sort>
                    <input type="hidden" name="per_page" value="{{ $perPage }}" data-filter-per-page>

                    <div class="filter-group">
                        <div class="filter-title">Categories</div>
                        <div class="filter-options category-filter-options">
                            @foreach($categories as $category)
                                <label class="filter-check">
                                    <input type="checkbox" name="category[]" value="{{ $category->slug }}" @checked($selectedCategorySlugs->contains($category->slug))>
                                    <span class="custom-check"><i class="fas fa-check"></i></span>
                                    <span class="filter-label">{{ $category->name }}</span>
                                    <small>({{ $category->active_products_count }})</small>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="filter-group">
                        <div class="filter-title">Price Range</div>
                        <div class="price-range" data-price-range data-min="0" data-max="{{ $globalMaxPrice }}">
                            <div class="range-track"><span data-range-fill></span></div>
                            <input type="range" min="0" max="{{ $globalMaxPrice }}" step="100" value="{{ (int) $minPrice }}" data-price-min-range aria-label="Minimum price">
                            <input type="range" min="0" max="{{ $globalMaxPrice }}" step="100" value="{{ (int) $maxPrice }}" data-price-max-range aria-label="Maximum price">
                        </div>
                        <div class="price-inputs">
                            <label><span>Min</span><div><b>৳</b><input type="number" min="0" max="{{ $globalMaxPrice }}" step="100" name="min_price" value="{{ (int) $minPrice }}" data-price-min></div></label>
                            <span class="price-divider">—</span>
                            <label><span>Max</span><div><b>৳</b><input type="number" min="0" max="{{ $globalMaxPrice }}" step="100" name="max_price" value="{{ (int) $maxPrice }}" data-price-max></div></label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <div class="filter-title">Availability</div>
                        <label class="filter-check">
                            <input type="checkbox" name="availability[]" value="in_stock" @checked($availability->contains('in_stock'))>
                            <span class="custom-check"><i class="fas fa-check"></i></span>
                            <span class="filter-label">In Stock</span>
                        </label>
                        <label class="filter-check">
                            <input type="checkbox" name="availability[]" value="out_of_stock" @checked($availability->contains('out_of_stock'))>
                            <span class="custom-check"><i class="fas fa-check"></i></span>
                            <span class="filter-label">Out of Stock</span>
                        </label>
                    </div>

                    <div class="filter-group">
                        <div class="filter-title">Highlights</div>
                        <label class="filter-check">
                            <input type="checkbox" name="featured" value="1" @checked($featuredOnly)>
                            <span class="custom-check"><i class="fas fa-check"></i></span>
                            <span class="filter-label">Featured Products</span>
                        </label>
                    </div>

                    <button type="submit" class="apply-filter-button"><i class="fas fa-filter"></i> Apply Filters</button>
                </form>
            </aside>

            <div class="shop-content">
                <div class="shop-content-head" data-shop-reveal>
                    <div>
                        <h2>{{ $selectedCategories->count() === 1 ? $selectedCategories->first()->name : 'All Products' }}</h2>
                        <p>{{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} available</p>
                    </div>
                    <label class="desktop-sort-control">
                        <span>Sort By:</span>
                        <select data-shop-sort-select>
                            <option value="featured" @selected($sort === 'featured')>Featured</option>
                            <option value="best_selling" @selected($sort === 'best_selling')>Best Selling</option>
                            <option value="newest" @selected($sort === 'newest')>Newest</option>
                            <option value="price_low" @selected($sort === 'price_low')>Price: Low to High</option>
                            <option value="price_high" @selected($sort === 'price_high')>Price: High to Low</option>
                            <option value="name_az" @selected($sort === 'name_az')>Name: A-Z</option>
                            <option value="name_za" @selected($sort === 'name_za')>Name: Z-A</option>
                        </select>
                    </label>
                </div>

                @if($searchTerm !== '' || $activeFilterCount > 0)
                    <div class="active-filter-bar" data-shop-reveal>
                        <div class="active-filter-copy">
                            <i class="fas fa-filter"></i>
                            <span>
                                @if($searchTerm !== '') Search: <strong>“{{ $searchTerm }}”</strong>@endif
                                @if($selectedCategorySlugs->isNotEmpty()) <strong>{{ $selectedCategorySlugs->count() }}</strong> categor{{ $selectedCategorySlugs->count() === 1 ? 'y' : 'ies' }} selected @endif
                            </span>
                        </div>
                        <a href="{{ route('website.shop') }}">Reset filters <i class="fas fa-times"></i></a>
                    </div>
                @endif

                @if($categories->isNotEmpty())
                    <div class="shop-category-carousel-wrap" data-shop-reveal>
                        <button type="button" class="category-scroll-button prev" data-category-strip-prev aria-label="Previous categories"><i class="fas fa-chevron-left"></i></button>
                        <div class="shop-category-strip" data-category-strip>
                            @foreach($categories as $category)
                                @php($activeCategory = $selectedCategorySlugs->contains($category->slug))
                                <a href="{{ route('website.shop', ['category' => [$category->slug]]) }}" class="shop-category-chip {{ $activeCategory ? 'active' : '' }}">
                                    <span>
                                        @if($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy">
                                        @else
                                            <img src="{{ asset('assets/website/images/category-placeholder.svg') }}" alt="{{ $category->name }}" loading="lazy">
                                        @endif
                                    </span>
                                    <strong>{{ $category->name }}</strong>
                                    <small>{{ $category->active_products_count }} items</small>
                                </a>
                            @endforeach
                        </div>
                        <button type="button" class="category-scroll-button next" data-category-strip-next aria-label="Next categories"><i class="fas fa-chevron-right"></i></button>
                    </div>
                @endif

                @if($products->count())
                    <div class="shop-product-grid">
                        @foreach($products as $product)
                            @include('website.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="shop-results-footer" data-shop-reveal>
                        <div class="results-summary">
                            Showing <strong>{{ $products->firstItem() }}</strong>–<strong>{{ $products->lastItem() }}</strong> of <strong>{{ $products->total() }}</strong> products
                        </div>

                        {{ $products->onEachSide(1)->links('website.partials.pagination') }}

                        <label class="per-page-control">
                            <span>Show:</span>
                            <select data-shop-per-page>
                                <option value="12" @selected($perPage === 12)>12 per page</option>
                                <option value="24" @selected($perPage === 24)>24 per page</option>
                                <option value="36" @selected($perPage === 36)>36 per page</option>
                            </select>
                        </label>
                    </div>
                @else
                    <div class="shop-empty premium-shop-empty" data-shop-reveal>
                        <div class="shop-empty-illustration"><span class="search-document"><i class="fas fa-list"></i></span><span class="search-lens"><i class="fas fa-search"></i></span></div>
                        <h3>No results found</h3>
                        <p>We couldn't find products matching your current search and filters. Try different keywords or browse all categories.</p>
                        <a href="{{ route('website.shop') }}" class="btn btn-primary"><i class="fas fa-th-large"></i> View All Products</a>
                    </div>
                @endif
            </div>
        </div>
        </div>
