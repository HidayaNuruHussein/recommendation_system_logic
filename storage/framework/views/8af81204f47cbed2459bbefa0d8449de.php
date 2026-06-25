
<?php
$showAiBadge = $showAiBadge ?? false;
$confidence = $confidence ?? 0;
$confidencePercent = $confidence > 0 ? round($confidence * 100) : 0;
?>
<article class="product-card ai-product-card <?php if($showAiBadge): ?> has-ai-badge <?php endif; ?>">
    <div class="product-image">
        <a href="<?php echo e(route('shop.show', ['public_id' => $product->public_id, 'slug' => $product->slug])); ?>" class="text-decoration-none">
            <img src="<?php echo e($product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('img/logo.png')); ?>"
                alt="<?php echo e($product->name); ?>" loading="lazy">
        </a>
        <div class="product-badges">
            <?php if($showAiBadge): ?>
            <span class="product-badge badge-ai" title="Recommended by our AI engine">
                <i class="bi bi-stars"></i> AI Pick
                <?php if($confidencePercent > 0): ?>
                <span class="badge-confidence"><?php echo e($confidencePercent); ?>%</span>
                <?php endif; ?>
            </span>
            <?php endif; ?>
            <?php if($product->created_at && $product->created_at->diffInDays(now()) <= 7): ?>
                <span class="product-badge badge-new">New</span>
                <?php endif; ?>
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
                In Stock
                <?php elseif($product->stock > 0): ?>
                Only <?php echo e($product->stock); ?> left
                <?php else: ?>
                Out of Stock
                <?php endif; ?>
            </span>
        </div>
        <?php if($product->category): ?>
        <div class="product-meta">
            <span class="category">
                <i class="bi bi-tag-fill"></i> <?php echo e($product->category->name); ?>

            </span>
        </div>
        <?php endif; ?>
    </div>
</article><?php /**PATH C:\Users\HIDAYA NURU\hidaya_fyp\resources\views/partials/ai-product-card.blade.php ENDPATH**/ ?>