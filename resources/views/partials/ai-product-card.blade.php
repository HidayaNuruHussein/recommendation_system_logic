{{--
Reusable product card partial for AI Recommendations and Related products.
Required variables:
  $product (Product model)
  $showAiBadge (bool, optional) - shows "AI Pick" badge
  $confidence (float, optional) - confidence score 0-1
--}}
@php
$showAiBadge = $showAiBadge ?? false;
$confidence = $confidence ?? 0;
$confidencePercent = $confidence > 0 ? round($confidence * 100) : 0;
@endphp
<article class="product-card ai-product-card @if($showAiBadge) has-ai-badge @endif">
    <div class="product-image">
        <a href="{{ route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug]) }}" class="text-decoration-none">
            <img src="{{ $product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png') }}"
                alt="{{ $product->name }}" loading="lazy">
        </a>
        <div class="product-badges">
            @if($showAiBadge)
            <span class="product-badge badge-ai" title="Recommended by our AI engine">
                <i class="bi bi-stars"></i> AI Pick
                @if($confidencePercent > 0)
                <span class="badge-confidence">{{ $confidencePercent }}%</span>
                @endif
            </span>
            @endif
            @if($product->created_at && $product->created_at->diffInDays(now()) <= 7)
                <span class="product-badge badge-new">New</span>
                @endif
        </div>
    </div>
    <div class="product-info">
        <h3 class="product-title">
            <a href="{{ route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug]) }}" class="text-decoration-none">
                {{ $product->name }}
            </a>
        </h3>
        @if($product->description?->description)
        <p class="product-description">
            {{ Str::limit($product->description->description, 60) }}
        </p>
        @endif
        <div class="product-prices">
            <span class="product-price">Tsh {{ number_format((float) $product->new_price, 0) }}</span>
            @if($product->old_price && $product->old_price > $product->new_price)
            <span class="product-old-price">Tsh {{ number_format((float) $product->old_price, 0) }}</span>
            @endif
        </div>
        <div class="product-rating">
            <div class="stars">
                @for($i = 1; $i <= 5; $i++)
                    @if($product->rate > 0)
                    <i class="bi {{ $i <= round($product->rate) ? 'bi-star-fill' : 'bi-star' }} star"></i>
                    @else
                    <i class="bi bi-star star text-secondary"></i>
                    @endif
                    @endfor
            </div>
            <span class="rating-count">({{ number_format((float) $product->rate, 1) }})</span>
            <span class="stock-status {{ $product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out') }}">
                @if($product->stock > 10)
                In Stock
                @elseif($product->stock > 0)
                Only {{ $product->stock }} left
                @else
                Out of Stock
                @endif
            </span>
        </div>
        @if($product->category)
        <div class="product-meta">
            <span class="category">
                <i class="bi bi-tag-fill"></i> {{ $product->category->name }}
            </span>
        </div>
        @endif
    </div>
</article>