<?php

namespace App\Services\Recommendation;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Cache;

class AssociationRuleService
{
    protected int $minSupportCount;
    protected float $minConfidence;
    protected int $maxRecommendations;
    protected string $cacheKey;
    protected int $cacheTtl;

    public function __construct(
        int $minSupportCount = 1,
        float $minConfidence = 0.15,
        int $maxRecommendations = 10,
        string $cacheKey = 'recommendation.association_rules',
        int $cacheTtl = 3600
    ) {
        $this->minSupportCount = $minSupportCount;
        $this->minConfidence = $minConfidence;
        $this->maxRecommendations = $maxRecommendations;
        $this->cacheKey = $cacheKey;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Get recommended product IDs for a single product.
     */
    public function recommendForProduct(int $productId): array
    {
        $rules = $this->getRules();

        if (! isset($rules[$productId])) {
            return [];
        }

        return collect($rules[$productId])
            ->sortByDesc('confidence')
            ->take($this->maxRecommendations)
            ->pluck('product_id')
            ->all();
    }

    /**
     * Get association rules from cache or generate and store them.
     */
    public function getRules(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return $this->generateRules();
        });
    }

    /**
     * Force refresh rules cache.
     */
    public function refreshRules(): array
    {
        $rules = $this->generateRules();
        Cache::put($this->cacheKey, $rules, $this->cacheTtl);
        return $rules;
    }

    /**
     * Generate rules from order item history.
     */
    protected function generateRules(): array
    {
        $transactions = $this->loadTransactions();

        if (empty($transactions)) {
            return [];
        }

        $transactionCount = count($transactions);
        $singleCounts = $this->countItemSets($transactions, 1);
        $pairCounts = $this->countItemSets($transactions, 2);
        $frequentPairs = $this->filterFrequentItemsets($pairCounts);

        return $this->generateAssociationRules($frequentPairs, $singleCounts, $transactionCount);
    }

    protected function loadTransactions(): array
    {
        $orderItems = OrderItem::query()
            ->select('order_items.order_id', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['completed', 'delivered'])
            ->get();

        return $orderItems
            ->groupBy('order_id')
            ->map(fn ($items) => $items->pluck('product_id')->unique()->sort()->values()->all())
            ->filter(fn ($items) => count($items) > 1)
            ->values()
            ->all();
    }

    /**
     * Count all combinations of itemsets of size $size.
     */
    protected function countItemSets(array $transactions, int $size): array
    {
        $counts = [];

        foreach ($transactions as $transaction) {
            $combinations = $this->combinations($transaction, $size);
            foreach ($combinations as $itemset) {
                $key = implode(',', $itemset);
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Filter itemsets by minimum support count.
     */
    protected function filterFrequentItemsets(array $itemsetCounts): array
    {
        return array_filter($itemsetCounts, fn ($count) => $count >= $this->minSupportCount);
    }

    protected function generateAssociationRules(array $pairCounts, array $singleCounts, int $transactionCount): array
    {
        $rules = [];

        foreach ($pairCounts as $pairKey => $count) {
            $pair = explode(',', $pairKey);
            if (count($pair) !== 2) {
                continue;
            }

            [$itemA, $itemB] = $pair;
            $supportA = $singleCounts[$itemA] ?? 0;
            $supportB = $singleCounts[$itemB] ?? 0;

            if ($supportA > 0) {
                $confidenceAtoB = $count / $supportA;
                if ($confidenceAtoB >= $this->minConfidence) {
                    $rules[(int) $itemA][] = [
                        'product_id' => (int) $itemB,
                        'confidence' => round($confidenceAtoB, 4),
                        'support' => round($count / $transactionCount, 4),
                    ];
                }
            }

            if ($supportB > 0) {
                $confidenceBtoA = $count / $supportB;
                if ($confidenceBtoA >= $this->minConfidence) {
                    $rules[(int) $itemB][] = [
                        'product_id' => (int) $itemA,
                        'confidence' => round($confidenceBtoA, 4),
                        'support' => round($count / $transactionCount, 4),
                    ];
                }
            }
        }

        return $rules;
    }

    protected function combinations(array $items, int $size): array
    {
        if ($size === 1) {
            return array_map(fn ($item) => [$item], $items);
        }

        $results = [];
        $count = count($items);

        for ($i = 0; $i <= $count - $size; $i++) {
            $head = [$items[$i]];
            $tailCombinations = $this->combinations(array_slice($items, $i + 1), $size - 1);
            foreach ($tailCombinations as $tail) {
                $results[] = array_merge($head, $tail);
            }
        }

        return $results;
    }
}
