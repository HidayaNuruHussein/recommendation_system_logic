<?php

namespace App\Services\Recommendation;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

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

    /**
     * Get recommendations for a single product using PHP Apriori rules.
     *
     * @return array List of recommended products with metadata
     */
    public function recommend(int $productId, int $limit = 8): array
    {
        $recs = $this->recommendWithMetadata($productId, $limit);
        if (empty($recs)) {
            return $this->fallbackForProduct($productId, $limit);
        }

        return $recs;
    }

    /**
     * Get recommendations with metadata (confidence, source).
     */
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

    /**
     * Get recommendations for multiple products (cart-based / order-based).
     */
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

    /**
     * Fallback: same category as the source product.
     */
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
        $recs = $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.0,
            'lift' => 0.0,
            'support' => 0.0,
            'source' => 'category_fallback',
        ])->all();
        if (count($recs) < $limit) {
            $more = $this->fallbackGeneral($limit - count($recs), array_merge($excludeIds, collect($recs)->pluck('product.id')->all()));
            $recs = array_merge($recs, $more);
        }

        return $recs;
    }

    /**
     * Final fallback: top rated & most viewed products.
     */
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

        return $products->map(fn ($p) => [
            'product' => $p,
            'confidence' => 0.0,
            'lift' => 0.0,
            'support' => 0.0,
            'source' => 'general_fallback',
        ])->all();
    }

    /**
     * Hydrate raw recommendation payload with full Product models.
     */
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
