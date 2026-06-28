<?php

namespace App\Services\Recommendation;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Recommendation Service (PHP only - Apriori Association Rules).
 *
 * Uses the local PHP AssociationRuleService for all recommendations.
 * No external Python service required.
 */
class HybridRecommendationService
{
    protected AssociationRuleService $phpService;

    public function __construct(?AssociationRuleService $phpService = null)
    {
        $this->phpService = $phpService ?? new AssociationRuleService;
    }

    // ============================================
    // ✅ HOMEPAGE RECOMMENDATIONS WITH PROXIMITY (OR LOGIC)
    // ============================================
    public function recommendForUserWithProximity(int $userId, int $limit = 8): array
    {
        Log::debug('recommendForUserWithProximity called', ['user_id' => $userId]);

        $preferredCategories = $this->getUserPreferredCategories($userId);
        
        if (empty($preferredCategories)) {
            Log::debug('No preferred categories found for user', ['user_id' => $userId]);
            return $this->fallbackGeneral($limit);
        }

        $allRecommendations = [];
        $excludeProductIds = [];

        foreach ($preferredCategories as $categoryId) {
            $relatedCategoryIds = $this->getRelatedCategoryIds($categoryId);
            
            if (empty($relatedCategoryIds)) {
                Log::debug('No related categories found for category', ['category_id' => $categoryId]);
                continue;
            }

            Log::debug('Related categories for user preference', [
                'category_id' => $categoryId,
                'related_ids' => $relatedCategoryIds
            ]);

            $products = Product::with(['category', 'media', 'description'])
                ->whereIn('category_id', $relatedCategoryIds)
                ->where('stock', '>', 0)
                ->whereNotIn('id', $excludeProductIds)
                ->orderByDesc('rate')
                ->orderByDesc('views')
                ->take(2)
                ->get();

            Log::debug('Products found for category', [
                'category_id' => $categoryId,
                'count' => $products->count()
            ]);

            foreach ($products as $product) {
                $allRecommendations[] = [
                    'product' => $product,
                    'confidence' => 0.7,
                    'source' => 'related_categories'
                ];
                $excludeProductIds[] = $product->id;
            }

            if (count($allRecommendations) >= $limit) {
                break;
            }
        }

        usort($allRecommendations, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        $allRecommendations = array_slice($allRecommendations, 0, $limit);

        Log::debug('recommendForUserWithProximity results', [
            'user_id' => $userId,
            'preferred_categories' => $preferredCategories,
            'count' => count($allRecommendations)
        ]);

        return $allRecommendations;
    }

    // ============================================
    // ✅ HOMEPAGE RECOMMENDATIONS (ORIGINAL)
    // ============================================
    public function recommendForUser(int $userId, int $limit = 8): array
    {
        Log::debug('recommendForUser called', ['user_id' => $userId]);

        $preferredCategories = $this->getUserPreferredCategories($userId);
        
        if (empty($preferredCategories)) {
            Log::debug('No preferred categories found for user', ['user_id' => $userId]);
            return $this->fallbackGeneral($limit);
        }

        $products = Product::with(['category', 'media', 'description'])
            ->whereIn('category_id', $preferredCategories)
            ->where('stock', '>', 0)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        Log::debug('recommendForUser results', [
            'user_id' => $userId,
            'categories' => $preferredCategories,
            'count' => $products->count()
        ]);

        return $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.6,
            'source' => 'user_preferences'
        ])->toArray();
    }

    // ============================================
    // ✅ PRODUCT PAGE: RECOMMEND BY CATEGORY PROXIMITY (OR LOGIC)
    // ============================================
    public function recommendByCategoryProximity(int $productId, int $limit = 8): array
    {
        Log::debug('recommendByCategoryProximity called', [
            'product_id' => $productId,
            'limit' => $limit
        ]);

        $product = Product::with(['category.parent'])->find($productId);
        if (!$product) {
            Log::debug('Product not found', ['product_id' => $productId]);
            return $this->fallbackGeneral($limit);
        }

        $category = $product->category;
        if (!$category) {
            Log::debug('Category not found for product', ['product_id' => $productId]);
            return $this->fallbackGeneral($limit);
        }

        Log::debug('Category details for proximity', [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'parent_id' => $category->parent_id,
            'category_group' => $category->category_group,
            'tags' => $category->tags
        ]);

        // ✅ GET CATEGORY PROXIMITY SCORES (Group priority)
        $categoryProximity = $this->getCategoryProximity($category->id);

        if (empty($categoryProximity)) {
            Log::debug('No category proximity found', [
                'product_id' => $productId,
                'category_id' => $category->id
            ]);
            return $this->fallbackForProduct($productId, $limit);
        }

        $categoryIds = array_keys($categoryProximity);
        
        Log::debug('Proximity categories found', [
            'category_ids' => $categoryIds,
            'proximity_scores' => $categoryProximity
        ]);

        $products = Product::with(['category', 'media', 'description'])
            ->whereIn('category_id', $categoryIds)
            ->where('id', '!=', $productId)
            ->where('stock', '>', 0)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->take($limit * 2)
            ->get();

        if ($products->isEmpty()) {
            Log::debug('No products found in proximity categories', [
                'product_id' => $productId,
                'category_id' => $category->id,
                'proximity_categories' => $categoryIds
            ]);
            return $this->fallbackForProduct($productId, $limit);
        }

        // ✅ Sort by proximity score (group first, then parent, etc.)
        $sortedProducts = $products->sortByDesc(function($p) use ($categoryProximity) {
            return $categoryProximity[$p->category_id] ?? 0;
        })->take($limit);

        Log::debug('recommendByCategoryProximity results', [
            'product_id' => $productId,
            'category_id' => $category->id,
            'category_name' => $category->name,
            'parent_id' => $category->parent_id,
            'category_group' => $category->category_group,
            'proximity_categories_count' => count($categoryIds),
            'result_count' => $sortedProducts->count()
        ]);

        return $sortedProducts->map(fn ($p) => [
            'product' => $p,
            'confidence' => $categoryProximity[$p->category_id] ?? 0.5,
            'source' => 'category_proximity'
        ])->toArray();
    }

    // ============================================
    // ✅ GET RELATED CATEGORY IDS (OR LOGIC)
    // ============================================
    private function getRelatedCategoryIds(int $categoryId): array
    {
        $category = Category::with(['parent', 'children'])->find($categoryId);
        if (!$category) {
            Log::warning('Category not found', ['category_id' => $categoryId]);
            return [];
        }

        $relatedIds = [];

        Log::debug('Finding related categories', [
            'category_id' => $categoryId,
            'category_name' => $category->name,
            'parent_id' => $category->parent_id,
            'category_group' => $category->category_group,
            'tags' => $category->tags
        ]);

        // ✅ 1. SAME CATEGORY (Always include)
        $relatedIds[] = $categoryId;
        Log::debug('Added same category', ['id' => $categoryId]);

        // ✅ 2. SAME CATEGORY GROUP (HIGHEST PRIORITY)
        if ($category->category_group && $category->category_group !== 'Other') {
            $sameGroup = Category::where('category_group', $category->category_group)
                ->where('id', '!=', $categoryId)
                ->pluck('id')
                ->toArray();
            $relatedIds = array_merge($relatedIds, $sameGroup);
            
            if (!empty($sameGroup)) {
                Log::debug('Added same group (HIGH PRIORITY)', [
                    'group' => $category->category_group,
                    'categories' => $sameGroup
                ]);
            }
        }

        // ✅ 3. SAME PARENT ID (if exists)
        if ($category->parent_id) {
            $relatedIds[] = $category->parent_id;
            Log::debug('Added parent category', ['parent_id' => $category->parent_id]);
            
            // Also include siblings (same parent)
            $siblings = Category::where('parent_id', $category->parent_id)
                ->where('id', '!=', $categoryId)
                ->pluck('id')
                ->toArray();
            $relatedIds = array_merge($relatedIds, $siblings);
            
            if (!empty($siblings)) {
                Log::debug('Added siblings', ['siblings' => $siblings]);
            }
        }

        // ✅ 4. CHILDREN CATEGORIES
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        $relatedIds = array_merge($relatedIds, $children);
        
        if (!empty($children)) {
            Log::debug('Added children', ['children' => $children]);
        }

        // ✅ 5. SIMILAR TAGS (if tags exist)
        if (!empty($category->tags)) {
            $tags = is_array($category->tags) ? $category->tags : json_decode($category->tags ?? '[]', true);
            if (!empty($tags)) {
                $similarTags = Category::where('id', '!=', $categoryId)
                    ->whereNotNull('tags')
                    ->get()
                    ->filter(function($c) use ($tags) {
                        $cTags = is_array($c->tags) ? $c->tags : json_decode($c->tags ?? '[]', true);
                        return count(array_intersect($tags, $cTags)) > 0;
                    })
                    ->pluck('id')
                    ->toArray();
                $relatedIds = array_merge($relatedIds, $similarTags);
                
                if (!empty($similarTags)) {
                    Log::debug('Added similar tags', [
                        'tags' => $tags,
                        'categories' => $similarTags
                    ]);
                }
            }
        }

        // ✅ 6. ORDER AFFINITY (frequently bought together)
        $affinity = $this->getCategoryAffinityFromOrders($categoryId);
        $affinityIds = array_keys($affinity);
        $relatedIds = array_merge($relatedIds, $affinityIds);
        
        if (!empty($affinityIds)) {
            Log::debug('Added order affinity', [
                'affinity' => $affinity
            ]);
        }

        // Remove duplicates
        $uniqueIds = array_values(array_unique($relatedIds));

        Log::debug('Related category IDs found', [
            'category_id' => $categoryId,
            'category_name' => $category->name,
            'total_related' => count($uniqueIds),
            'related_ids' => $uniqueIds
        ]);

        return $uniqueIds;
    }

    // ============================================
    // ✅ DYNAMIC CATEGORY PROXIMITY
    // ============================================
    private function getCategoryProximity(int $categoryId): array
    {
        $category = Category::with(['parent', 'children'])->find($categoryId);
        if (!$category) {
            return [];
        }

        $proximity = [];
        $proximity[$categoryId] = 1.0;

        Log::debug('Calculating category proximity', [
            'category_id' => $categoryId,
            'category_name' => $category->name,
            'parent_id' => $category->parent_id,
            'category_group' => $category->category_group,
            'tags' => $category->tags
        ]);

        // ✅ 1. SAME CATEGORY (1.0) - Already set

        // ✅ 2. SAME CATEGORY GROUP (0.9) - HIGHEST PRIORITY
        if ($category->category_group && $category->category_group !== 'Other') {
            $sameGroup = Category::where('category_group', $category->category_group)
                ->where('id', '!=', $categoryId)
                ->pluck('id')
                ->toArray();
            foreach ($sameGroup as $groupId) {
                if (!isset($proximity[$groupId]) || $proximity[$groupId] < 0.9) {
                    $proximity[$groupId] = 0.9;
                }
            }
            if (!empty($sameGroup)) {
                Log::debug('Added same group (HIGH PRIORITY)', [
                    'group' => $category->category_group,
                    'categories' => $sameGroup,
                    'score' => 0.9
                ]);
            }
        }

        // ✅ 3. PARENT CATEGORY (0.85)
        if ($category->parent_id) {
            $proximity[$category->parent_id] = 0.85;
            Log::debug('Added parent category', [
                'parent_id' => $category->parent_id,
                'score' => 0.85
            ]);
        }

        // ✅ 4. SIBLINGS (same parent) - 0.8
        if ($category->parent_id) {
            $siblings = Category::where('parent_id', $category->parent_id)
                ->where('id', '!=', $categoryId)
                ->pluck('id')
                ->toArray();
            foreach ($siblings as $siblingId) {
                if (!isset($proximity[$siblingId]) || $proximity[$siblingId] < 0.8) {
                    $proximity[$siblingId] = 0.8;
                }
            }
            if (!empty($siblings)) {
                Log::debug('Added sibling categories', [
                    'siblings' => $siblings,
                    'count' => count($siblings),
                    'score' => 0.8
                ]);
            }
        }

        // ✅ 5. CHILDREN CATEGORIES - 0.8
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            if (!isset($proximity[$childId]) || $proximity[$childId] < 0.8) {
                $proximity[$childId] = 0.8;
            }
        }
        if (!empty($children)) {
            Log::debug('Added child categories', [
                'children' => $children,
                'count' => count($children),
                'score' => 0.8
            ]);
        }

        // ✅ 6. SIMILAR TAGS - 0.5
        if (!empty($category->tags)) {
            $tags = is_array($category->tags) ? $category->tags : json_decode($category->tags ?? '[]', true);
            if (!empty($tags)) {
                $similarTags = Category::where('id', '!=', $categoryId)
                    ->whereNotNull('tags')
                    ->get()
                    ->filter(function($c) use ($tags) {
                        $cTags = is_array($c->tags) ? $c->tags : json_decode($c->tags ?? '[]', true);
                        return count(array_intersect($tags, $cTags)) > 0;
                    })
                    ->pluck('id')
                    ->toArray();
                foreach ($similarTags as $tagId) {
                    if (!isset($proximity[$tagId]) || $proximity[$tagId] < 0.5) {
                        $proximity[$tagId] = 0.5;
                    }
                }
                if (!empty($similarTags)) {
                    Log::debug('Added similar tags categories', [
                        'tags' => $tags,
                        'categories' => $similarTags,
                        'count' => count($similarTags),
                        'score' => 0.5
                    ]);
                }
            }
        }

        // ✅ 7. ORDER AFFINITY - 0.6 to 0.8
        $affinity = $this->getCategoryAffinityFromOrders($categoryId);
        foreach ($affinity as $affinityId => $score) {
            if (!isset($proximity[$affinityId]) || $proximity[$affinityId] < $score) {
                $proximity[$affinityId] = $score;
            }
        }
        if (!empty($affinity)) {
            Log::debug('Added order affinity categories', [
                'affinity' => $affinity,
                'score_range' => '0.6 - 0.8'
            ]);
        }

        arsort($proximity);

        Log::debug('Category proximity calculated', [
            'category_id' => $categoryId,
            'category_name' => $category->name,
            'parent_id' => $category->parent_id,
            'category_group' => $category->category_group,
            'proximity_count' => count($proximity),
            'top_proximity' => array_slice($proximity, 0, 5)
        ]);

        return $proximity;
    }

    // ============================================
    // ✅ HELPER: Get category affinity from orders
    // ============================================
    private function getCategoryAffinityFromOrders(int $categoryId): array
    {
        $affinity = DB::table('order_items as oi1')
            ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
            ->join('products as p1', 'oi1.product_id', '=', 'p1.id')
            ->join('products as p2', 'oi2.product_id', '=', 'p2.id')
            ->where('p1.category_id', $categoryId)
            ->where('p1.category_id', '!=', 'p2.category_id')
            ->select('p2.category_id', DB::raw('COUNT(*) as frequency'))
            ->groupBy('p2.category_id')
            ->orderBy('frequency', 'DESC')
            ->limit(5)
            ->pluck('frequency', 'p2.category_id')
            ->toArray();

        $maxFrequency = max($affinity) ?: 1;
        $scored = [];
        foreach ($affinity as $id => $freq) {
            $scored[$id] = 0.5 + (($freq / $maxFrequency) * 0.3);
        }

        return $scored;
    }

    // ============================================
    // ✅ HELPER: Get user preferred categories
    // ============================================
    private function getUserPreferredCategories(int $userId): array
    {
        $categories = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $userId)
            ->whereIn('orders.status', ['completed', 'delivered'])
            ->select('products.category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category_id')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->pluck('products.category_id')
            ->toArray();

        if (!empty($categories)) {
            Log::debug('Preferred categories from orders', [
                'user_id' => $userId,
                'categories' => $categories
            ]);
            return $categories;
        }

        $categories = DB::table('product_views')
            ->join('products', 'products.id', '=', 'product_views.product_id')
            ->where('product_views.user_id', $userId)
            ->select('products.category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category_id')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->pluck('products.category_id')
            ->toArray();

        Log::debug('Preferred categories from views', [
            'user_id' => $userId,
            'categories' => $categories
        ]);

        return $categories;
    }

    // ============================================
    // ✅ PRODUCT PAGE: RECOMMEND BY CATEGORY ONLY (SIMPLE)
    // ============================================
    public function recommendByCategory(int $productId, int $limit = 8): array
    {
        Log::debug('recommendByCategory called', ['product_id' => $productId]);

        $product = Product::find($productId);
        if (!$product) {
            Log::debug('Product not found for category recommendations', ['product_id' => $productId]);
            return [];
        }

        $products = Product::with(['category', 'media', 'description'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('stock', '>', 0)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        if ($products->isEmpty()) {
            Log::debug('No products found in same category', [
                'product_id' => $productId,
                'category_id' => $product->category_id,
                'category_name' => $product->category->name ?? 'Unknown'
            ]);
            return [];
        }

        Log::debug('recommendByCategory results', [
            'product_id' => $productId,
            'category_id' => $product->category_id,
            'category_name' => $product->category->name ?? 'Unknown',
            'count' => $products->count()
        ]);

        return $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.8,
            'source' => 'same_category'
        ])->toArray();
    }

    // ============================================
    // ✅ PRODUCT PAGE: SIMILAR PRODUCTS (SAME CATEGORY ONLY)
    // ============================================
    public function recommendSimilar(int $productId, int $limit = 8): array
    {
        Log::debug('recommendSimilar called', ['product_id' => $productId]);

        $product = Product::find($productId);
        if (!$product) {
            Log::debug('Product not found for similar recommendations', ['product_id' => $productId]);
            return [];
        }

        $query = Product::with(['category', 'media', 'description'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('stock', '>', 0);

        if ($product->brand) {
            $sameBrand = (clone $query)
                ->where('brand', $product->brand)
                ->orderByDesc('rate')
                ->orderByDesc('views')
                ->take($limit)
                ->get();

            if ($sameBrand->count() >= $limit) {
                return $sameBrand->map(fn ($p) => [
                    'product' => $p,
                    'confidence' => 0.9,
                    'source' => 'same_category_brand'
                ])->toArray();
            }

            $excludeIds = $sameBrand->pluck('id')->toArray();
            $remaining = (clone $query)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('rate')
                ->orderByDesc('views')
                ->take($limit - $sameBrand->count())
                ->get();

            $results = $sameBrand->merge($remaining);
        } else {
            $results = $query
                ->orderByDesc('rate')
                ->orderByDesc('views')
                ->take($limit)
                ->get();
        }

        Log::debug('recommendSimilar results', [
            'product_id' => $productId,
            'count' => $results->count(),
            'category_id' => $product->category_id,
            'category_name' => $product->category->name ?? 'Unknown'
        ]);

        if ($results->isEmpty()) {
            Log::debug('No similar products found in same category', [
                'product_id' => $productId,
                'category_id' => $product->category_id
            ]);
            return [];
        }

        return $results->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.7,
            'source' => 'similar_products'
        ])->toArray();
    }

    // ============================================
    // ✅ EXISTING METHODS
    // ============================================

    public function recommend(int $productId, int $limit = 8): array
    {
        $recs = $this->recommendWithMetadata($productId, $limit);
        if (empty($recs)) {
            return $this->fallbackForProduct($productId, $limit);
        }
        return $recs;
    }

    public function recommendWithMetadata(int $productId, int $limit = 8): array
    {
        $ids = $this->phpService->recommendForProduct($productId);
        if (! empty($ids)) {
            $products = Product::with(['category', 'media', 'description'])
                ->whereIn('id', $ids)
                ->where('stock', '>', 0)
                ->take($limit)
                ->get();
            $recs = $products->map(fn ($p) => [
                'product' => $p,
                'confidence' => 0.0,
                'lift' => 0.0,
                'support' => 0.0,
                'source' => 'php_local',
            ])->values()->all();
            if (! empty($recs)) {
                Log::debug('Recommendations served by PHP Apriori', [
                    'product_id' => $productId,
                    'count' => count($recs),
                ]);
                return $recs;
            }
        }
        return [];
    }

    public function recommendForCart(array $productIds, int $limit = 8): array
    {
        $productIds = array_values(array_unique(array_filter($productIds)));
        if (empty($productIds)) {
            return [];
        }

        $scores = [];
        $meta = [];

        foreach ($productIds as $pid) {
            $ids = $this->phpService->recommendForProduct((int) $pid);
            foreach ($ids as $id) {
                if (in_array($id, $productIds, true)) {
                    continue;
                }
                $scores[$id] = ($scores[$id] ?? 0) + 1;
                $meta[$id] = ['confidence' => 0, 'lift' => 0, 'support' => 0];
            }
        }

        if (empty($scores)) {
            $lastId = end($productIds);
            $ids = $this->phpService->recommendForProduct((int) $lastId);
            foreach ($ids as $id) {
                if (in_array($id, $productIds, true)) {
                    continue;
                }
                $scores[$id] = 0.5;
                $meta[$id] = ['confidence' => 0, 'lift' => 0, 'support' => 0];
            }
        }

        if (empty($scores)) {
            return $this->fallbackGeneral($limit);
        }

        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, $limit, true);
        $products = Product::with(['category', 'media', 'description'])
            ->whereIn('id', $topIds)
            ->where('stock', '>', 0)
            ->get()
            ->keyBy('id');
        $ordered = collect($topIds)
            ->map(function ($id) use ($products, $meta) {
                $p = $products->get($id);
                if (! $p) {
                    return null;
                }
                $m = $meta[$id] ?? [];

                return [
                    'product' => $p,
                    'confidence' => $m['confidence'] ?? 0,
                    'lift' => $m['lift'] ?? 0,
                    'support' => $m['support'] ?? 0,
                    'source' => 'php_local',
                ];
            })
            ->filter()
            ->values()
            ->all();
        if (count($ordered) < $limit) {
            $more = $this->fallbackGeneral($limit - count($ordered), array_merge($productIds, $topIds));
            $ordered = array_merge($ordered, $more);
        }

        return $ordered;
    }

    public function fallbackForProduct(int $productId, int $limit = 8, array $excludeIds = []): array
    {
        $product = Product::find($productId);
        if (! $product) {
            return $this->fallbackGeneral($limit, $excludeIds);
        }
        $excludeIds = array_values(array_unique(array_merge([$productId], $excludeIds)));
        $products = Product::with(['category', 'media', 'description'])
            ->where('category_id', $product->category_id)
            ->where('stock', '>', 0)
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        Log::debug('fallbackForProduct called', [
            'product_id' => $productId,
            'category_id' => $product->category_id,
            'count' => $products->count()
        ]);

        return $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.5,
            'source' => 'same_category'
        ])->toArray();
    }

    public function fallbackGeneral(int $limit = 8, array $excludeIds = []): array
    {
        $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
        $products = Product::with(['category', 'media', 'description'])
            ->where('stock', '>', 0)
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        Log::debug('fallbackGeneral called', [
            'count' => $products->count()
        ]);

        return $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.3,
            'source' => 'general_fallback'
        ])->toArray();
    }

    protected function hydrateWithProducts(array $raw, int $limit, string $source): array
    {
        $ids = collect($raw)->pluck('product_id')->all();
        $products = Product::with(['category', 'media', 'description'])
            ->whereIn('id', $ids)
            ->where('stock', '>', 0)
            ->get()
            ->keyBy('id');
        $recs = [];
        foreach ($raw as $rec) {
            $pid = $rec['product_id'] ?? null;
            $p = $pid ? $products->get($pid) : null;
            if (! $p) {
                continue;
            }
            $recs[] = [
                'product' => $p,
                'confidence' => $rec['confidence'] ?? 0,
                'lift' => $rec['lift'] ?? 0,
                'support' => $rec['support'] ?? 0,
                'source' => $source,
            ];
            if (count($recs) >= $limit) {
                break;
            }
        }
        return $recs;
    }
}