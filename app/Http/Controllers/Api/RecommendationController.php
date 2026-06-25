<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Recommendation\AssociationRuleService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function __invoke(int $productId, AssociationRuleService $service): JsonResponse
    {
        $product = Product::find($productId);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $recommendationIds = $service->recommendForProduct($productId);

        $products = Product::query()
            ->whereIn('id', $recommendationIds)
            ->get(['id', 'public_id', 'name', 'slug', 'new_price', 'old_price', 'thumbnail'])
            ->keyBy('id');

        $sortedProducts = collect($recommendationIds)
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->values();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'public_id' => $product->public_id,
                'slug' => $product->slug,
            ],
            'recommendations' => $sortedProducts,
        ]);
    }
}
