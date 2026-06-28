

<?php $__env->startSection('title', 'Shop - electronicStore'); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/shop.css')); ?>">
<style>
    /* ============================================ */
    /* AI RECOMMENDATIONS - RESPONSIVE GRID */
    /* ============================================ */
    .ai-products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    .ai-products-grid .product-card {
        width: 100%;
        min-width: 0;
    }

    @media (max-width: 1200px) {
        .ai-products-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 992px) {
        .ai-products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .ai-products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .ai-products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
    }

    /* ============================================ */
    /* BADGES */
    /* ============================================ */
    .badge-recommended {
        background: linear-gradient(135deg, #f7971e, #ffd200);
        color: #000;
        font-size: 0.55rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 700;
    }

    .badge-category {
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: #fff;
        font-size: 0.55rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-parent {
        background: #6c757d;
        color: #fff;
        font-size: 0.5rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .badge-group {
        background: #0d6efd;
        color: #fff;
        font-size: 0.5rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .badge-tags {
        background: #20c997;
        color: #fff;
        font-size: 0.5rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    /* ============================================ */
    /* AI PRODUCT CARD */
    /* ============================================ */
    .ai-product-card .product-info {
        padding: 0.5rem 0.25rem;
    }

    .ai-product-card .product-title {
        font-size: 0.8rem;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .ai-product-card .product-title a {
        color: #2d3436;
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ai-product-card .product-title a:hover {
        color: #0984e3;
    }

    .ai-product-card .product-description {
        font-size: 0.7rem;
        color: #636e72;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 0.25rem;
    }

    .ai-product-card .product-prices {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .ai-product-card .product-price {
        font-size: 0.85rem;
        font-weight: 700;
        color: #2d3436;
    }

    .ai-product-card .product-old-price {
        font-size: 0.7rem;
        color: #b2bec3;
        text-decoration: line-through;
    }

    .ai-product-card .product-rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.65rem;
    }

    .ai-product-card .product-rating .stars {
        display: flex;
        gap: 1px;
    }

    .ai-product-card .product-rating .stars .star {
        font-size: 0.6rem;
        color: #fdcb6e;
    }

    .ai-product-card .product-rating .stars .star.text-secondary {
        color: #dfe6e9;
    }

    .ai-product-card .rating-count {
        color: #636e72;
    }

    .ai-product-card .product-image {
        position: relative;
        overflow: hidden;
        border-radius: 8px 8px 0 0;
        background: #f8f9fa;
    }

    .ai-product-card .product-image img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .ai-product-card .product-image img:hover {
        transform: scale(1.02);
    }

    .ai-product-card .product-badges {
        position: absolute;
        top: 6px;
        left: 6px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .ai-product-card .product-badges .product-badge {
        font-size: 0.5rem;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .ai-product-card .product-badges .badge-new {
        background: #0984e3;
        color: #fff;
    }

    .ai-product-card .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid #f0f0f0;
    }

    .ai-product-card .product-meta .category {
        font-size: 0.65rem;
        color: #636e72;
    }

    .ai-product-card .product-meta .category i {
        font-size: 0.55rem;
    }

    .recommendation-tag {
        font-size: 0.6rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .recommendation-tag i {
        font-size: 0.55rem;
    }

    .category-info-row {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }

    .category-info-row .badge {
        font-size: 0.5rem;
        padding: 2px 6px;
        border-radius: 10px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="shop-container">
<?php
    $hasActiveFilter = filled(request('search')) || filled(request('category'));
?>
<!-- Search and Categories in Header -->
<div class="shop-header-sticky">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Search Bar Section -->
                <div class="search-section mb-3">
                    <?php
                        $selectedSort = request('sort_by') && request('sort_order') ? request('sort_by') . '-' . request('sort_order') : '';
                    ?>
                    <form method="GET" action="<?php echo e(route('shop')); ?>" id="shop-search-form">
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap flex-md-nowrap search-sort-row">
                            <div class="search-bar position-relative search-bar-compact">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo e(request('search')); ?>">
                                <button type="submit" class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <select class="form-select form-select-sm shop-sort-select" id="shop-sort-select" aria-label="Sort products">
                                <option value="">Sort by</option>
                                <option value="created_at-desc" <?php echo e($selectedSort === 'created_at-desc' ? 'selected' : ''); ?>>Newest</option>
                                <option value="name-asc" <?php echo e($selectedSort === 'name-asc' ? 'selected' : ''); ?>>Name (A-Z)</option>
                                <option value="name-desc" <?php echo e($selectedSort === 'name-desc' ? 'selected' : ''); ?>>Name (Z-A)</option>
                                <option value="new_price-asc" <?php echo e($selectedSort === 'new_price-asc' ? 'selected' : ''); ?>>Price (Low to High)</option>
                                <option value="new_price-desc" <?php echo e($selectedSort === 'new_price-desc' ? 'selected' : ''); ?>>Price (High to Low)</option>
                                <option value="rate-desc" <?php echo e($selectedSort === 'rate-desc' ? 'selected' : ''); ?>>Highest Rated</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Categories Section -->
                <div class="categories-header-section">
                    <div class="categories-grid d-flex gap-2 justify-content-start" id="shop-categories-grid">
                        <?php
                            $allCategoriesQuery = request()->query();
                            unset($allCategoriesQuery['category']);
                        ?>
                        <a href="<?php echo e(route('shop')); ?>?<?php echo e(http_build_query($allCategoriesQuery)); ?>" class="category-pill btn btn-sm <?php echo e(!request('category') ? 'category-pill-selected' : 'category-pill-default'); ?>">
                            <i class="bi bi-grid-fill me-1"></i>All Categories
                        </a>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $categoryQuery = request()->query();
                                $categoryQuery['category'] = $category->id;
                            ?>
                        <a href="<?php echo e(route('shop')); ?>?<?php echo e(http_build_query($categoryQuery)); ?>" class="category-pill btn btn-sm <?php echo e(request('category') == $category->id ? 'category-pill-selected' : 'category-pill-default'); ?>">
                                <?php echo e($category->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controls Bar -->
<div class="controls-bar" style="display: none;">
</div>

<!-- ============================================ -->
<!-- 1. PRODUCT RECOMMENDATIONS (HOMEPAGE) -->
<!-- ============================================ -->
<?php if(Auth::check() && !empty($aiRecommendations) && count($aiRecommendations) > 0): ?>
    <section class="mb-4 ai-recommendations-section featured-row-panel" id="ai-recommendations-section">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 fw-bold mb-0 featured-row-title">
                <i class="bi bi-robot me-2"></i>
                <span>Recommended Products</span>
            </h2>
            <?php if(isset($aiRecommendations[0]['source'])): ?>
                <span class="badge bg-info text-white small">
                    <i class="bi bi-robot me-1"></i>AI Powered
                </span>
            <?php endif; ?>
        </div>
        <?php if(isset($aiRecommendations[0]['source'])): ?>
            <p class="text-muted small mb-3">
                <i class="bi bi-lightbulb me-1"></i>
                Based on your browsing history and preferences
            </p>
        <?php endif; ?>
        <div class="products-grid ai-products-grid">
            <?php $__currentLoopData = $aiRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $aiProduct = $rec['product'] ?? null;
                    $confidence = $rec['confidence'] ?? 0;
                    $source = $rec['source'] ?? 'general';
                    
                    $sourceLabels = [
                        'user_preferences' => 'Based on your interests',
                        'same_category_brand' => 'Same brand & category',
                        'similar_products' => 'Products you might like',
                        'same_category' => 'Same category',
                        'category_proximity' => 'Related categories',
                        'general_fallback' => 'Popular products',
                        'apriori_cart' => 'Frequently bought together',
                        'complementary' => 'Complementary products'
                    ];
                    $sourceLabel = $sourceLabels[$source] ?? 'Recommended for you';
                    
                    $category = $aiProduct->category;
                    $categoryName = $category->name ?? 'Uncategorized';
                    $parentName = $category->parent->name ?? null;
                    $groupName = $category->category_group ?? null;
                    $tags = $category->tags ?? [];
                ?>
                <?php if($aiProduct): ?>
                    <div class="product-card ai-product-card">
                        <div class="product-image">
                            <a href="<?php echo e(route('shop.show', ['public_id' => $aiProduct->public_id, 'slug' => $aiProduct->slug])); ?>" class="text-decoration-none">
                                <img src="<?php echo e($aiProduct->thumbnail ? asset('storage/' . $aiProduct->thumbnail) : asset('img/logo.png')); ?>" alt="<?php echo e($aiProduct->name); ?>" loading="lazy">
                            </a>
                            <div class="product-badges">
                                <?php if($aiProduct->created_at->diffInDays(now()) <= 7): ?>
                                    <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                                <?php if($confidence > 0.7): ?>
                                    <span class="product-badge badge-recommended">★ Recommended</span>
                                <?php endif; ?>
                                <span class="product-badge badge-category"><?php echo e($categoryName); ?></span>
                                <!-- <?php if($parentName): ?>
                                    <span class="product-badge badge-parent"><?php echo e($parentName); ?></span>
                                <?php endif; ?>
                                <?php if($groupName && $groupName !== 'Other'): ?>
                                    <span class="product-badge badge-group"><?php echo e($groupName); ?></span>
                                <?php endif; ?> -->
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="<?php echo e(route('shop.show', ['public_id' => $aiProduct->public_id, 'slug' => $aiProduct->slug])); ?>" class="text-decoration-none">
                                    <?php echo e($aiProduct->name); ?>

                                </a>
                            </h3>
                            <?php if($aiProduct->description?->description): ?>
                                <p class="product-description">
                                    <?php echo e(Str::limit($aiProduct->description->description, 60)); ?>

                                </p>
                            <?php endif; ?>
                            <div class="product-prices">
                                <span class="product-price">Tsh <?php echo e(number_format((float) $aiProduct->new_price, 0)); ?></span>
                                <?php if($aiProduct->old_price && $aiProduct->old_price > $aiProduct->new_price): ?>
                                    <span class="product-old-price">Tsh <?php echo e(number_format((float) $aiProduct->old_price, 0)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($aiProduct->rate > 0): ?>
                                            <i class="bi <?php echo e($i <= round($aiProduct->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star star text-secondary"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo e(number_format((float) $aiProduct->rate, 1)); ?>)</span>
                            </div>
                            <div class="product-meta">
                                <span class="category">
                                    <i class="bi bi-tag-fill"></i> <?php echo e($categoryName); ?>

                                </span>
                                <span class="recommendation-tag small text-muted">
                                    <i class="bi bi-info-circle"></i> <?php echo e($sourceLabel); ?>

                                </span>
                            </div>
                            <div class="category-info-row">
                                <!-- <?php if($parentName): ?>
                                    <span class="badge badge-parent">
                                        <i class="bi bi-diagram-3 me-1"></i><?php echo e($parentName); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($groupName && $groupName !== 'Other'): ?>
                                    <span class="badge badge-group">
                                        <i class="bi bi-grid me-1"></i><?php echo e($groupName); ?>

                                    </span>
                                <?php endif; ?> -->
                                <?php if(!empty($tags) && is_array($tags)): ?>
                                    <?php $__currentLoopData = array_slice($tags, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge badge-tags">
                                            <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(count($tags) > 2): ?>
                                        <span class="badge badge-tags">+<?php echo e(count($tags) - 2); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mobile-scroll-indicator" aria-hidden="true">
            <span class="mobile-scroll-thumb"></span>
        </div>
    </section>
<?php endif; ?>

<!-- ============================================ -->
<!-- 2. FEATURED ROWS -->
<!-- ============================================ -->
<?php if(!$hasActiveFilter && !empty($featuredRows)): ?>
    <div class="mb-4 featured-rows-scroll" id="featured-rows-container">
        <?php $__currentLoopData = ['row1', 'row2', 'row3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $rowProducts = $featuredRows[$rowKey] ?? collect();
                $rowTitle = $featuredRows[$rowKey . '_title'] ?? '';
                $rowIcon = match($rowKey) {
                    'row1' => 'bi-box-seam',
                    'row2' => 'bi-star',
                    'row3' => 'bi-eye',
                    default => 'bi-grid-fill'
                };
            ?>
            <?php if($rowProducts->isNotEmpty()): ?>
                <section class="mb-4 featured-row-section featured-row-panel">
                    <h2 class="h5 fw-bold mb-3 featured-row-title">
                        <i class="bi <?php echo e($rowIcon); ?> me-2"></i><?php echo e($rowTitle); ?>

                    </h2>
                    <div class="products-grid featured-products-grid">
                        <?php $__currentLoopData = $rowProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $cat = $product->category;
                                $parentName = $cat->parent->name ?? null;
                                $groupName = $cat->category_group ?? null;
                            ?>
                            <article class="product-card featured-product-card">
                                <div class="product-image">
                                    <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                                        <img src="<?php echo e($product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png')); ?>" alt="<?php echo e($product->name); ?>" loading="lazy">
                                    </a>
                                    <div class="product-badges">
                                        <?php if($product->created_at->diffInDays(now()) <= 7): ?>
                                            <span class="product-badge badge-new">New</span>
                                        <?php endif; ?>
                                        <!-- <?php if($parentName): ?>
                                            <span class="product-badge badge-parent"><?php echo e($parentName); ?></span>
                                        <?php endif; ?>
                                        <?php if($groupName && $groupName !== 'Other'): ?>
                                            <span class="product-badge badge-group"><?php echo e($groupName); ?></span>
                                        <?php endif; ?> -->
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                                            <?php echo e($product->name); ?>

                                        </a>
                                    </h3>
                                    <?php if($product->description?->description): ?>
                                        <p class="product-description">
                                            <?php echo e(Str::limit($product->description->description, 60)); ?>

                                        </p>
                                    <?php endif; ?>
                                    <div class="product-prices">
                                        <span class="product-price">Tsh <?php echo e(number_format((float) $product->new_price, 0)); ?></span>
                                        <?php if($product->old_price && $product->old_price > $product->new_price): ?>
                                            <span class="product-old-price">Tsh <?php echo e(number_format((float) $product->old_price, 0)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($product->rate > 0): ?>
                                                    <i class="bi <?php echo e($i <= round($product->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star star text-secondary"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-count">(<?php echo e(number_format((float) $product->rate, 1)); ?>)</span>
                                        <span class="stock-status <?php echo e($product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                            <?php if($product->stock > 10): ?>
                                                In Stock: <?php echo e($product->stock); ?>

                                            <?php elseif($product->stock > 0): ?>
                                                In Stock: <?php echo e($product->stock); ?>

                                            <?php else: ?>
                                                Out of Stock
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mobile-scroll-indicator" aria-hidden="true">
                        <span class="mobile-scroll-thumb"></span>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- 3. PRODUCTS GRID -->
<!-- ============================================ -->
<div id="search-loading-spinner" class="text-center py-4" style="display: none;">
    <div class="loading-spinner"></div>
    <p class="text-muted mt-2 small">Searching products...</p>
</div>

<div class="products-grid" id="productsContainer">
    <?php if($products->count() > 0): ?>
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $cat = $product->category;
                $parentName = $cat->parent->name ?? null;
                $groupName = $cat->category_group ?? null;
                $tags = $cat->tags ?? [];
            ?>
            <article class="product-card">
                <div class="product-image">
                    <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                        <img src="<?php echo e($product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png')); ?>" alt="<?php echo e($product->name); ?>" loading="lazy">
                    </a>
                    <div class="product-badges">
                        <?php if($product->created_at->diffInDays(now()) <= 7): ?>
                            <span class="product-badge badge-new">New</span>
                        <?php endif; ?>
                        <!-- <?php if($parentName): ?>
                            <span class="product-badge badge-parent"><?php echo e($parentName); ?></span>
                        <?php endif; ?>
                        <?php if($groupName && $groupName !== 'Other'): ?>
                            <span class="product-badge badge-group"><?php echo e($groupName); ?></span>
                        <?php endif; ?> -->
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-title">
                        <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
                            <?php echo e($product->name); ?>

                        </a>
                    </h3>
                    <?php if($product->description?->description): ?>
                        <p class="product-description">
                            <?php echo e(Str::limit($product->description->description, 60)); ?>

                        </p>
                    <?php endif; ?>
                    <div class="product-prices">
                        <span class="product-price">Tsh <?php echo e(number_format((float) $product->new_price, 0)); ?></span>
                        <?php if($product->old_price && $product->old_price > $product->new_price): ?>
                            <span class="product-old-price">Tsh <?php echo e(number_format((float) $product->old_price, 0)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($product->rate > 0): ?>
                                    <i class="bi <?php echo e($i <= round($product->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                <?php else: ?>
                                    <i class="bi bi-star star text-secondary"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count">(<?php echo e(number_format((float) $product->rate, 1)); ?>)</span>
                        <span class="stock-status <?php echo e($product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                            <?php if($product->stock > 10): ?>
                                In Stock: <?php echo e($product->stock); ?>

                            <?php elseif($product->stock > 0): ?>
                                In Stock: <?php echo e($product->stock); ?>

                            <?php else: ?>
                                Out of Stock
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="product-meta">
                        <span class="category">
                            <i class="bi bi-tag-fill"></i> <?php echo e($cat->name ?? 'Uncategorized'); ?>

                        </span>
                    </div>
                    <div class="category-info-row">
                        <?php if($parentName): ?>
                            <span class="badge badge-parent">
                                <i class="bi bi-diagram-3 me-1"></i><?php echo e($parentName); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($groupName && $groupName !== 'Other'): ?>
                            <span class="badge badge-group">
                                <i class="bi bi-grid me-1"></i><?php echo e($groupName); ?>

                            </span>
                        <?php endif; ?>
                        <?php if(!empty($tags) && is_array($tags)): ?>
                            <?php $__currentLoopData = array_slice($tags, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge badge-tags">
                                    <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(count($tags) > 2): ?>
                                <span class="badge badge-tags">+<?php echo e(count($tags) - 2); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="no-products-found">
            <div class="shop-empty text-center p-4 p-md-5">
                <i class="bi bi-search d-inline-block mb-2"></i>
                <h3 class="h5 mb-2">No products found</h3>
                <p class="mb-3">Try adjusting your search criteria or browse different categories</p>
                <?php if(Auth::check() && !empty($aiRecommendations) && count($aiRecommendations) > 0): ?>
                    <div class="mt-4">
                        <p class="text-muted small fw-bold mb-2">
                            <i class="bi bi-lightbulb me-1"></i> Meanwhile, check these recommendations:
                        </p>
                        <div class="products-grid ai-products-grid mt-3">
                            <?php $__currentLoopData = array_slice($aiRecommendations, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $aiProduct = $rec['product'] ?? null; ?>
                                <?php if($aiProduct): ?>
                                    <div class="product-card ai-product-card">
                                        <div class="product-image">
                                            <a href="<?php echo e(route('shop.show', ['public_id' => $aiProduct->public_id, 'slug' => $aiProduct->slug])); ?>" class="text-decoration-none">
                                                <img src="<?php echo e($aiProduct->thumbnail ? asset('storage/' . $aiProduct->thumbnail) : asset('img/logo.png')); ?>" alt="<?php echo e($aiProduct->name); ?>" loading="lazy">
                                            </a>
                                            <div class="product-badges">
                                                <span class="product-badge badge-category"><?php echo e($aiProduct->category->name ?? 'Uncategorized'); ?></span>
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title">
                                                <a href="<?php echo e(route('shop.show', ['public_id' => $aiProduct->public_id, 'slug' => $aiProduct->slug])); ?>" class="text-decoration-none">
                                                    <?php echo e($aiProduct->name); ?>

                                                </a>
                                            </h3>
                                            <div class="product-prices">
                                                <span class="product-price">Tsh <?php echo e(number_format((float) $aiProduct->new_price, 0)); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <a href="<?php echo e(route('shop')); ?>" class="btn shop-empty-btn mt-3">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<div id="shop-pagination" style="display:none;">
    <?php echo e($products->appends(request()->query())->links()); ?>

</div>
<div id="shop-infinite-loader" class="text-center py-3" style="display:none;">
    <span class="loading-spinner"></span>
</div>
<div id="shop-infinite-end" class="text-center py-2 text-muted small" style="display:none;">
    No more products
</div>
<div id="shop-scroll-sentinel" style="height: 1px;"></div>

</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/shop.js')); ?>"></script>
<script src="<?php echo e(asset('js/ai-recommendations.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\recommendation_system_logic\resources\views/shop.blade.php ENDPATH**/ ?>