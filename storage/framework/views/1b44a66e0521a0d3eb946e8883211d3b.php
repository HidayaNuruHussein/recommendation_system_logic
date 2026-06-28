

<?php $__env->startSection('title', $product->name . ' - electronicStore'); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/shop.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/show.css')); ?>">
    <style>
        /* ✅ AI RECOMMENDATIONS GRID - RESPONSIVE */
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

        /* ✅ BADGE STYLES */
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

        .badge-recommended {
            background: linear-gradient(135deg, #f7971e, #ffd200);
            color: #000;
            font-size: 0.55rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 700;
        }

        .badge-complementary {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: #fff;
            font-size: 0.55rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* ✅ RECOMMENDATION TAG */
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

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #f0f0f0;
        }

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

        .ai-product-card .stock-status {
            font-size: 0.6rem;
            padding: 1px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .ai-product-card .stock-status.stock-in {
            color: #00b894;
            background: #e8f8f5;
        }

        .ai-product-card .stock-status.stock-low {
            color: #fdcb6e;
            background: #fff8e1;
        }

        .ai-product-card .stock-status.stock-out {
            color: #e17055;
            background: #fdf0ed;
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

        .complementary-products-section .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 992px) {
            .complementary-products-section .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .complementary-products-section .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .complementary-products-section .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }

        /* ✅ RELATED PRODUCTS GRID */
        .category-related-products .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 992px) {
            .category-related-products .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .category-related-products .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .category-related-products .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }

        /* ✅ CATEGORY INFO ROW */
        .category-info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #f0f0f0;
        }

        .category-info-row .badge {
            font-size: 0.5rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        /* ✅ PRODUCT CATEGORY INFO IN HEADER */
        .product-category-info {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }

        .product-category-info .badge {
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-category-main {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: #fff;
        }

        .badge-parent-main {
            background: #6c757d;
            color: #fff;
        }

        .badge-group-main {
            background: #0d6efd;
            color: #fff;
        }

        .badge-tags-main {
            background: #20c997;
            color: #fff;
        }
    </style>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;">
        <div class="notification-content">
            <i class="bi bi-check-circle"></i>
            <span id="notification-message"></span>
        </div>
    </div>

    <div class="container py-3 py-md-4">
    <!-- Shop Menu Icon -->
    <div class="shop-menu-icon mb-3">
        <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline-secondary btn-sm show-dot-btn" data-spin-link="1" title="Back to Shop">
            <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="button-text"><i class="bi bi-arrow-left"></i> Back to Shop</span>
        </a>
    </div>

    <div class="product-details">
        <div class="row g-4">
            <div class="col-lg-6">
                <!-- Product Gallery -->
                <div class="product-gallery card border-0 shadow-sm p-2 p-md-3">
                    <!-- Top row: Main image and left horizontal thumbnails -->
                    <div class="gallery-top-row">
                        <div class="gallery-sidebar">
                            <div class="gallery-thumbs-vertical">
                                <?php
                                    $images = $product->media->where('type', 'image') ?: collect();
                                    $primaryImage = $images->where('is_primary', true)->first() ?: $images->first();
                                ?>

                                <?php if($primaryImage): ?>
                                    <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="thumb-image <?php echo e($loop->first ? 'active' : ''); ?>"
                                            data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                            onclick="changeImage('<?php echo e(asset('storage/' . $image->file_path)); ?>', this)">
                                            <img src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                                alt="<?php echo e($product->name); ?>">
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="main-image" id="mainImageFrame">
                            <?php
                                $mainImage = $primaryImage
                                    ? asset('storage/' . $primaryImage->file_path)
                                    : asset('img/logo.png');
                            ?>
                            <?php if($primaryImage && $images->count() > 1): ?>
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-prev"
                                    onclick="galleryPrev()"
                                    aria-label="Previous image">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button
                                    type="button"
                                    class="gallery-nav-btn gallery-nav-next"
                                    onclick="galleryNext()"
                                    aria-label="Next image">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                            <img id="mainImage" src="<?php echo e($mainImage); ?>" alt="<?php echo e($product->name); ?>">
                        </div>
                    </div>

                    <?php if($primaryImage && $images->count() > 1): ?>
                        <div class="mobile-gallery-dots">
                            <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    class="gallery-dot <?php echo e($loop->first ? 'active' : ''); ?>"
                                    data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                    onclick="selectImageBySrc('<?php echo e(asset('storage/' . $image->file_path)); ?>')"
                                    aria-label="View image <?php echo e($loop->iteration); ?>">
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Bottom row: Horizontal thumbnails -->
                    <?php if($primaryImage && $images->count() > 1): ?>
                        <div class="horizontal-thumbs-container">
                            <div class="gallery-thumbs-horizontal">
                                <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="thumb-image-horizontal <?php echo e($loop->first ? 'active' : ''); ?>"
                                        data-image-src="<?php echo e(asset('storage/' . $image->file_path)); ?>"
                                        onclick="changeImage('<?php echo e(asset('storage/' . $image->file_path)); ?>', this)">
                                        <img src="<?php echo e(asset('storage/' . $image->file_path)); ?>" alt="<?php echo e($product->name); ?>">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Product Info -->
                <div class="product-info card border-0 shadow-sm p-3 p-md-4">
                    <h1 class="product-main-title mb-2 fw-bold lh-sm"><?php echo e($product->name); ?>

                        <?php if($product->created_at > \Carbon\Carbon::now()->subDays(30)): ?>
                            <span class="badge bg-success align-middle">NEW</span>
                        <?php endif; ?>
                    </h1>

                    <?php
                        $allFiveStars = $product->ratings->count() > 0
                            && $product->ratings->every(fn ($review) => (int) $review->rating === 5);
                        $averageRating = $allFiveStars
                            ? 5.0
                            : round((float) ($product->ratings->avg('rating') ?? 0), 1);
                        $totalReviews = $product->ratings->count();
                    ?>
                    <?php if($averageRating > 0): ?>
                        <div class="product-rating">
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php
                                        $starValue = $averageRating - ($i - 1);
                                    ?>
                                    <i class="bi <?php echo e($starValue >= 1 ? 'bi-star-fill' : ($starValue >= 0.5 ? 'bi-star-half' : 'bi-star')); ?> star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo e(number_format($averageRating, 1)); ?> (<?php echo e($totalReviews); ?> reviews)</span>
                        </div>
                    <?php endif; ?>

                    <div class="product-prices align-items-center">
                        <span class="current-price fw-bold">Tsh <?php echo e(number_format((float) $product->new_price, 0)); ?></span>
                        <?php if($product->old_price && $product->old_price > $product->new_price): ?>
                            <span class="old-price text-danger">Tsh <?php echo e(number_format((float) $product->old_price, 0)); ?></span>
                            <span class="badge bg-danger badge-sm"><?php echo e($product->discount); ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <div class="stock-qty-row">
                        <div
                            class="stock-info <?php echo e($product->stock > 10 ? 'stock-in' : ($product->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                            <i class="bi bi-circle-fill"></i>
                            <?php if($product->stock > 10): ?>
                                <span>In Stock (<?php echo e($product->stock); ?> available)</span>
                            <?php elseif($product->stock > 0): ?>
                                <span>Only <?php echo e($product->stock); ?> left in stock</span>
                            <?php else: ?>
                                <span>Out of Stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="quantity-selector">
                            <div class="quantity-input">
                                <button class="quantity-btn" onclick="changeQuantity(-1)" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>-</button>
                                <input type="number" id="quantityInput" value="<?php echo e($product->stock > 0 ? 1 : 0); ?>" min="1"
                                    max="<?php echo e($product->stock); ?>" readonly>
                                <button class="quantity-btn" onclick="changeQuantity(1)" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>+</button>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ CATEGORY INFO IN PRODUCT HEADER -->
                    <?php
                        $category = $product->category;
                        $parentName = $category?->parent?->name ?? null;
                        $groupName = $category?->category_group ?? null;
                        $tags = $category?->tags ?? [];
                    ?>
                    <?php if($category): ?>
                        <div class="product-category-info">
                            <span class="badge badge-category-main">
                                <i class="bi bi-tag me-1"></i><?php echo e($category->name); ?>

                            </span>
                            <?php if($parentName): ?>
                                <span class="badge badge-parent-main">
                                    <i class="bi bi-diagram-3 me-1"></i><?php echo e($parentName); ?>

                                </span>
                            <?php endif; ?>
                            <?php if($groupName && $groupName !== 'Other'): ?>
                                <span class="badge badge-group-main">
                                    <i class="bi bi-grid me-1"></i><?php echo e($groupName); ?>

                                </span>
                            <?php endif; ?>
                            <?php if(!empty($tags) && is_array($tags)): ?>
                                <?php $__currentLoopData = array_slice($tags, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge badge-tags-main">
                                        <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if(count($tags) > 3): ?>
                                    <span class="badge badge-tags-main">+<?php echo e(count($tags) - 3); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="action-buttons row g-2">
                        <div class="col-12">
                        <button class="btn-add-cart w-100 show-dot-btn"
                            <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>

                            onclick="addToCart(<?php echo e($product->id); ?>, document.getElementById('quantityInput').value)">
                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                            <span class="button-text"><i class="bi bi-cart-plus"></i> <?php echo e($product->stock <= 0 ? 'Out of Stock' : 'Add to Cart'); ?></span>
                        </button>
                        </div>
                        <?php if(auth()->guard()->check()): ?>
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 show-dot-btn" onclick="openRatingModal()">
                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="button-text">
                                    <i class="bi bi-star"></i>
                                    <span class="d-none d-sm-inline">Rate this Product</span>
                                    <span class="d-inline d-sm-none">Rate This Product</span>
                                </span>
                            </button>
                            </div>
                        <?php endif; ?>
                    </div>


                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-buttons-scrollable">
                <button class="tab-btn active" onclick="switchTab('description')">
                    <i class="bi bi-info-circle"></i>
                    <span>Description</span>
                </button>
                <button class="tab-btn" onclick="switchTab('specifications')">
                    <i class="bi bi-gear"></i>
                    <span>Specifications</span>
                </button>
                <button class="tab-btn" onclick="switchTab('reviews')">
                    <i class="bi bi-star"></i>
                    <span>Reviews</span>
                </button>
            </div>

            <div id="description" class="tab-content active">
                <h4>About this product</h4>
                <div class="prose">
                    <?php echo $product->description->description ?? '<p>No description available.</p>'; ?>

                    <?php echo $product->description->details ?? ''; ?>

                </div>
            </div>

            <div id="specifications" class="tab-content">
                <h4>Technical Specifications</h4>
                <div class="prose">
                    <?php if($product->description && $product->description->specifications): ?>
                        <?php
                            $specs = trim($product->description->specifications);
                            $lines = array_filter(explode("\n", $specs), function ($line) {
                                return trim($line) !== '';
                            });
                            if (count($lines) > 0) {
                                echo '<ul class="spec-list">';
                                foreach ($lines as $line) {
                                    if (strpos($line, ':') !== false) {
                                        [$key, $value] = explode(':', $line, 2);
                                        echo '<li><strong>' .
                                            htmlspecialchars(trim($key)) .
                                            ':</strong> ' .
                                            htmlspecialchars(trim($value)) .
                                            '</li>';
                                    } else {
                                        echo '<li>' . htmlspecialchars(trim($line)) . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>No specifications available.</p>';
                            }
                        ?>
                    <?php else: ?>
                        <p>No specifications available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="reviews" class="tab-content">
                <div class="reviews-heading">
                    <h4>Customer Reviews</h4>
                    <?php if($totalReviews > 0): ?>
                        <div class="reviews-heading-meta">
                            <span class="reviews-score"><?php echo e(number_format($averageRating, 1)); ?></span>
                            <div class="reviews-stars-inline">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo e($i <= round($averageRating) ? 'bi-star-fill' : 'bi-star'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="reviews-count"><?php echo e($totalReviews); ?> reviews</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($product->ratings->count() > 0): ?>
                    <div class="reviews-carousel-container">
                        <div class="css-carousel" id="cssReviewsCarousel">
                            <div class="css-carousel-track" style="--total-slides: <?php echo e($product->ratings->count()); ?>">
                                <?php $__currentLoopData = $product->ratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="css-carousel-slide">
                                        <div class="review-item">
                                            <div class="review-item-head">
                                                <div class="review-user">
                                                    <div class="review-avatar">
                                                        <?php echo e(strtoupper(substr($rating->user->name ?? 'U', 0, 1))); ?>

                                                    </div>
                                                    <div class="review-user-meta">
                                                        <strong class="review-author"><?php echo e($rating->user->name); ?></strong>
                                                        <small class="review-date">
                                                            <i class="bi bi-calendar3"></i>
                                                            <span><?php echo e($rating->created_at->format('M d, Y')); ?></span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="review-rating-summary">
                                                    <div class="stars review-stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i
                                                                class="bi <?php echo e($i <= $rating->rating ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="review-rating-badge"><?php echo e(number_format((float) $rating->rating, 1)); ?>/5</span>
                                                </div>
                                            </div>
                                            <?php if($rating->review): ?>
                                                <blockquote class="review-quote">
                                                    <p><?php echo e($rating->review); ?></p>
                                                </blockquote>
                                            <?php else: ?>
                                                <p class="review-empty">No review text provided</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>


                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No reviews yet. Be the first to rate this product!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 1. FREQUENTLY BOUGHT TOGETHER (Complementary) -->
        <!-- ============================================ -->
        <?php if(!empty($complementaryProducts) && count($complementaryProducts) > 0): ?>
            <section class="related-products complementary-products-section">
                <h2 class="related-title">
                    <i class="bi bi-basket3 me-2"></i>Frequently Bought Together
                </h2>
                <p class="text-muted small mb-3">Customers who bought this also bought these</p>
                <div class="products-grid">
                    <?php $__currentLoopData = $complementaryProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $compProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            if (is_array($compProduct) && isset($compProduct['product'])) {
                                $compProduct = $compProduct['product'];
                            }
                            $compCategory = $compProduct->category ?? null;
                            $compParentName = $compCategory?->parent?->name ?? null;
                            $compGroupName = $compCategory?->category_group ?? null;
                            $compTags = $compCategory?->tags ?? [];
                        ?>
                        <?php if($compProduct): ?>
                            <article class="product-card">
                                <div class="product-image">
                                    <a href="<?php echo e(route('shop.show', ['public_id' => $compProduct->public_id, 'slug' => $compProduct->slug])); ?>" class="text-decoration-none">
                                        <img src="<?php echo e($compProduct->thumbnail ? asset('storage/' . $compProduct->thumbnail) : asset('img/logo.png')); ?>"
                                            alt="<?php echo e($compProduct->name); ?>" loading="lazy">
                                    </a>
                                    <div class="product-badges">
                                        <?php if($compProduct->created_at->diffInDays(now()) <= 7): ?>
                                            <span class="product-badge badge-new">New</span>
                                        <?php endif; ?>
                                        <span class="product-badge badge-complementary">🛒 Bought Together</span>
                                        <?php if($compParentName): ?>
                                            <span class="product-badge badge-parent"><?php echo e($compParentName); ?></span>
                                        <?php endif; ?>
                                        <?php if($compGroupName && $compGroupName !== 'Other'): ?>
                                            <span class="product-badge badge-group"><?php echo e($compGroupName); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="<?php echo e(route('shop.show', ['public_id' => $compProduct->public_id, 'slug' => $compProduct->slug])); ?>" class="text-decoration-none">
                                            <?php echo e($compProduct->name); ?>

                                        </a>
                                    </h3>

                                    <?php if($compProduct->description?->description): ?>
                                        <p class="product-description">
                                            <?php echo e(Str::limit($compProduct->description->description, 60)); ?>

                                        </p>
                                    <?php endif; ?>

                                    <div class="product-prices">
                                        <span class="product-price">Tsh <?php echo e(number_format((float) $compProduct->new_price, 0)); ?></span>
                                        <?php if($compProduct->old_price && $compProduct->old_price > $compProduct->new_price): ?>
                                            <span class="product-old-price">Tsh <?php echo e(number_format((float) $compProduct->old_price, 0)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($compProduct->rate > 0): ?>
                                                    <i class="bi <?php echo e($i <= round($compProduct->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star star text-secondary"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-count">(<?php echo e(number_format((float) $compProduct->rate, 1)); ?>)</span>
                                        <span class="stock-status <?php echo e($compProduct->stock > 10 ? 'stock-in' : ($compProduct->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                            <?php if($compProduct->stock > 10): ?>
                                                In Stock
                                            <?php elseif($compProduct->stock > 0): ?>
                                                Only <?php echo e($compProduct->stock); ?> left
                                            <?php else: ?>
                                                Out of Stock
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <div class="product-meta">
                                        <span class="category">
                                            <i class="bi bi-tag-fill"></i> <?php echo e($compCategory->name ?? 'Uncategorized'); ?>

                                        </span>
                                    </div>
                                    <!-- ✅ Category Info Row -->
                                    <div class="category-info-row">
                                        <?php if($compParentName): ?>
                                            <span class="badge badge-parent">
                                                <i class="bi bi-diagram-3 me-1"></i><?php echo e($compParentName); ?>

                                            </span>
                                        <?php endif; ?>
                                        <?php if($compGroupName && $compGroupName !== 'Other'): ?>
                                            <span class="badge badge-group">
                                                <i class="bi bi-grid me-1"></i><?php echo e($compGroupName); ?>

                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($compTags) && is_array($compTags)): ?>
                                            <?php $__currentLoopData = array_slice($compTags, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="badge badge-tags">
                                                    <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(count($compTags) > 2): ?>
                                                <span class="badge badge-tags">+<?php echo e(count($compTags) - 2); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- 2. AI RECOMMENDATIONS (Similar Products) -->
        <!-- ============================================ -->
        <?php if(!empty($aiRecommendations) && count($aiRecommendations) > 0): ?>
            <section class="related-products category-related-products" id="ai-related-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="related-title mb-0">
                        <i class="bi bi-robot me-2"></i>
                        <?php echo e($hasOrderHistory ? 'Recommended Products' : 'You May Also Like'); ?>

                    </h2>
                    <span class="badge bg-info text-white small">
                        <i class="bi bi-robot me-1"></i>AI Powered
                    </span>
                </div>
                <p class="text-muted small mb-3">
                    <i class="bi bi-lightbulb me-1"></i>
                    <?php echo e($hasOrderHistory ? 'Based on your purchase history' : 'Similar products from the same category'); ?>

                </p>
                <div class="products-grid ai-products-grid">
                    <?php $__currentLoopData = $aiRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $aiProduct = $rec['product'] ?? null;
                            $confidence = $rec['confidence'] ?? 0;
                            $source = $rec['source'] ?? 'general';
                            
                            // ✅ UPDATED SOURCE LABELS
                            $sourceLabels = [
                                'user_preferences' => 'Based on your interests',
                                'same_category_brand' => 'Same brand & category',
                                'similar_products' => 'Similar products',
                                'same_category' => 'Same category',
                                'category_proximity' => 'Related categories',
                                'general_fallback' => 'Popular products',
                                'apriori_cart' => 'Frequently bought together',
                                'complementary' => 'Complementary products'
                            ];
                            $sourceLabel = $sourceLabels[$source] ?? 'Recommended for you';
                            
                            $aiCategory = $aiProduct->category ?? null;
                            $aiCategoryName = $aiCategory->name ?? 'Uncategorized';
                            $aiParentName = $aiCategory?->parent?->name ?? null;
                            $aiGroupName = $aiCategory?->category_group ?? null;
                            $aiTags = $aiCategory?->tags ?? [];
                        ?>
                        <?php if($aiProduct): ?>
                            <div class="product-card ai-product-card">
                                <div class="product-image">
                                    <a href="<?php echo e(route('shop.show', ['public_id' => $aiProduct->public_id, 'slug' => $aiProduct->slug])); ?>" class="text-decoration-none">
                                        <img src="<?php echo e($aiProduct->thumbnail ? asset('storage/' . $aiProduct->thumbnail) : asset('img/logo.png')); ?>"
                                            alt="<?php echo e($aiProduct->name); ?>" loading="lazy">
                                    </a>
                                    <div class="product-badges">
                                        <?php if($aiProduct->created_at->diffInDays(now()) <= 7): ?>
                                            <span class="product-badge badge-new">New</span>
                                        <?php endif; ?>
                                        <?php if($confidence > 0.7): ?>
                                            <span class="product-badge badge-recommended">★ Recommended</span>
                                        <?php endif; ?>
                                        <span class="product-badge badge-category"><?php echo e($aiCategoryName); ?></span>
                                        <?php if($aiParentName): ?>
                                            <span class="product-badge badge-parent"><?php echo e($aiParentName); ?></span>
                                        <?php endif; ?>
                                        <?php if($aiGroupName && $aiGroupName !== 'Other'): ?>
                                            <span class="product-badge badge-group"><?php echo e($aiGroupName); ?></span>
                                        <?php endif; ?>
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
                                        <span class="stock-status <?php echo e($aiProduct->stock > 10 ? 'stock-in' : ($aiProduct->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                            <?php if($aiProduct->stock > 10): ?>
                                                In Stock
                                            <?php elseif($aiProduct->stock > 0): ?>
                                                Only <?php echo e($aiProduct->stock); ?> left
                                            <?php else: ?>
                                                Out of Stock
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <div class="product-meta">
                                        <span class="category">
                                            <i class="bi bi-tag-fill"></i> <?php echo e($aiCategoryName); ?>

                                        </span>
                                        <span class="recommendation-tag small text-muted">
                                            <i class="bi bi-info-circle"></i> <?php echo e($sourceLabel); ?>

                                        </span>
                                    </div>
                                    <!-- ✅ Category Info Row -->
                                    <div class="category-info-row">
                                        <?php if($aiParentName): ?>
                                            <span class="badge badge-parent">
                                                <i class="bi bi-diagram-3 me-1"></i><?php echo e($aiParentName); ?>

                                            </span>
                                        <?php endif; ?>
                                        <?php if($aiGroupName && $aiGroupName !== 'Other'): ?>
                                            <span class="badge badge-group">
                                                <i class="bi bi-grid me-1"></i><?php echo e($aiGroupName); ?>

                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($aiTags) && is_array($aiTags)): ?>
                                            <?php $__currentLoopData = array_slice($aiTags, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="badge badge-tags">
                                                    <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(count($aiTags) > 2): ?>
                                                <span class="badge badge-tags">+<?php echo e(count($aiTags) - 2); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- 3. RELATED PRODUCTS (Category-based fallback) -->
        <!-- ============================================ -->
        <?php if($relatedProducts->count() > 0): ?>
            <section class="related-products category-related-products">
                <h2 class="related-title">
                    <i class="bi bi-tags me-2"></i>Related Products
                </h2>
                <p class="text-muted small mb-3">More products from this category</p>
                <div class="products-grid">
                    <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $relCategory = $relatedProduct->category ?? null;
                            $relParentName = $relCategory?->parent?->name ?? null;
                            $relGroupName = $relCategory?->category_group ?? null;
                            $relTags = $relCategory?->tags ?? [];
                        ?>
                        <article class="product-card">
                            <div class="product-image">
                                <a href="<?php echo e(route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug])); ?>" class="text-decoration-none">
                                    <img src="<?php echo e($relatedProduct->thumbnail ? asset('storage/' . $relatedProduct->thumbnail) : asset('img/logo.png')); ?>"
                                        alt="<?php echo e($relatedProduct->name); ?>" loading="lazy">
                                </a>
                                <div class="product-badges">
                                    <?php if($relatedProduct->created_at->diffInDays(now()) <= 7): ?>
                                        <span class="product-badge badge-new">New</span>
                                    <?php endif; ?>
                                    <?php if($relParentName): ?>
                                        <span class="product-badge badge-parent"><?php echo e($relParentName); ?></span>
                                    <?php endif; ?>
                                    <?php if($relGroupName && $relGroupName !== 'Other'): ?>
                                        <span class="product-badge badge-group"><?php echo e($relGroupName); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="<?php echo e(route('shop.show', ['public_id' => $relatedProduct->public_id, 'slug' => $relatedProduct->slug])); ?>" class="text-decoration-none">
                                        <?php echo e($relatedProduct->name); ?>

                                    </a>
                                </h3>

                                <?php if($relatedProduct->description?->description): ?>
                                    <p class="product-description">
                                        <?php echo e(Str::limit($relatedProduct->description->description, 60)); ?>

                                    </p>
                                <?php endif; ?>

                                <div class="product-prices">
                                    <span class="product-price">Tsh <?php echo e(number_format((float) $relatedProduct->new_price, 0)); ?></span>
                                    <?php if($relatedProduct->old_price && $relatedProduct->old_price > $relatedProduct->new_price): ?>
                                        <span class="product-old-price">Tsh <?php echo e(number_format((float) $relatedProduct->old_price, 0)); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-rating">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($relatedProduct->rate > 0): ?>
                                                <i class="bi <?php echo e($i <= round($relatedProduct->rate) ? 'bi-star-fill' : 'bi-star'); ?> star"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star star text-secondary"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?php echo e(number_format((float) $relatedProduct->rate, 1)); ?>)</span>
                                    <span class="stock-status <?php echo e($relatedProduct->stock > 10 ? 'stock-in' : ($relatedProduct->stock > 0 ? 'stock-low' : 'stock-out')); ?>">
                                        <?php if($relatedProduct->stock > 10): ?>
                                            In Stock: <?php echo e($relatedProduct->stock); ?>

                                        <?php elseif($relatedProduct->stock > 0): ?>
                                            In Stock: <?php echo e($relatedProduct->stock); ?>

                                        <?php else: ?>
                                            Out of Stock
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="product-meta">
                                    <span class="category">
                                        <i class="bi bi-tag-fill"></i> <?php echo e($relCategory->name ?? 'Uncategorized'); ?>

                                    </span>
                                </div>
                                <!-- ✅ Category Info Row -->
                                <div class="category-info-row">
                                    <?php if($relParentName): ?>
                                        <span class="badge badge-parent">
                                            <i class="bi bi-diagram-3 me-1"></i><?php echo e($relParentName); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if($relGroupName && $relGroupName !== 'Other'): ?>
                                        <span class="badge badge-group">
                                            <i class="bi bi-grid me-1"></i><?php echo e($relGroupName); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($relTags) && is_array($relTags)): ?>
                                        <?php $__currentLoopData = array_slice($relTags, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge badge-tags">
                                                <i class="bi bi-tag me-1"></i><?php echo e($tag); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(count($relTags) > 2): ?>
                                            <span class="badge badge-tags">+<?php echo e(count($relTags) - 2); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rating-modal-content">
                <div class="modal-header rating-modal-header">
                    <h5 class="modal-title rating-modal-title" id="ratingModalLabel">
                        <i class="bi bi-star-fill"></i>
                        <span>Rate this Product</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('shop.rate', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Your Rating</label>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?php echo e($i); ?>" name="rating"
                                        value="<?php echo e($i); ?>" class="d-none" required>
                                    <label for="star<?php echo e($i); ?>" class="bi bi-star-fill star-rating"></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review" class="form-label">Your Review (Optional)</label>
                            <textarea class="form-control" id="review" name="review" rows="3"
                                placeholder="Share your thoughts about this product..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn rating-submit-btn" id="submitRatingBtn">
                            <i class="bi bi-send"></i> Submit Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
        $currentUserRole = auth()->check() ? auth()->user()->role : null;
        $currentUserDashboardUrl = route('login');
        if (auth()->check()) {
            $currentUserDashboardUrl = match (auth()->user()->role) {
                'admin' => route('admin.dashboard'),
                'seller' => route('seller.dashboard'),
                default => route('customer.dashboard'),
            };
        }
    ?>
    <script>
        window.productId = <?php echo e($product->id); ?>;
        window.productPublicId = <?php echo json_encode($product->public_id, 15, 512) ?>;
        window.productSlug = <?php echo json_encode($product->slug, 15, 512) ?>;
        window.productViewActivityUrl = <?php echo json_encode(route('shop.view.activity', ['public_id' => $product->public_id, 'slug' => $product->slug])) ?>;
        window.currentUserRole = <?php echo json_encode($currentUserRole, 15, 512) ?>;
        window.currentUserEmailVerified = <?php echo json_encode(auth()->check() && auth()->user()->hasVerifiedEmail(), 15, 512) ?>;
        window.currentUserDashboardUrl = <?php echo json_encode($currentUserDashboardUrl, 15, 512) ?>;
    </script>

    
<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/show.js')); ?>"></script>
    <script src="<?php echo e(asset('js/show-rating.js')); ?>"></script>
    <script src="<?php echo e(asset('js/ai-recommendations.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\recommendation_system_logic\resources\views/shop/show.blade.php ENDPATH**/ ?>