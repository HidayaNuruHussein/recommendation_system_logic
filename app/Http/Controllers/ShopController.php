<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Rating;
use App\Services\Recommendation\HybridRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    private const MIN_VIEW_SECONDS_TO_RECORD = 10;

    private const FEATURE_REFRESH_HOURS = 2;

    public function index(Request $request)
    {
        $query = Product::with(['category', 'media', 'description']);

        // Only show products with stock > 0
        $query->where('stock', '>', 0);

        // Search
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhereHas('description', function ($desc) use ($search) {
                        $desc->where('description', 'like', '%'.$search.'%')
                            ->orWhere('specifications', 'like', '%'.$search.'%')
                            ->orWhere('details', 'like', '%'.$search.'%');
                    });
            });
        }
        // Category filter
        if ($request->has('category') && ! empty($request->category)) {
            $query->where('category_id', (int) $request->category);
        }

        // Rating filter
        if ($request->has('rating') && ! empty($request->rating)) {
            $query->where('rate', '>=', $request->rating);
        }

        // Discount filter
        if ($request->has('on_sale') && $request->on_sale == '1') {
            $query->where('discount', '>', 0);
        }

        // Sorting
        $featuredRows = [];
        $featuredProductIds = [];
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order;

            if (in_array($sortBy, ['name', 'new_price', 'created_at', 'rate']) && in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $topRatedIds = $this->topRatedProductIds(16);
            $topViewedIds = $this->topViewedProductIds(16, $topRatedIds);

            $priorityMap = [];
            $newestIds = Product::where('stock', '>', 0)
                ->orderByDesc('created_at')
                ->limit(16)
                ->pluck('id')
                ->all();

            foreach ($newestIds as $id) {
                $priorityMap[$id] = 1;
            }
            foreach ($topRatedIds as $id) {
                if (! isset($priorityMap[$id])) {
                    $priorityMap[$id] = 2;
                }
            }
            foreach ($topViewedIds as $id) {
                if (! isset($priorityMap[$id])) {
                    $priorityMap[$id] = 3;
                }
            }

            if (! empty($priorityMap)) {
                $priorityCases = collect($priorityMap)
                    ->map(fn ($priority, $id) => "WHEN id = {$id} THEN {$priority}")
                    ->implode(' ');

                $query->orderByRaw("CASE {$priorityCases} ELSE 4 END");
            }

            $query->orderByDesc('rate')
                ->orderByDesc('views')
                ->orderByDesc('created_at');

$featuredRows = [
                'row1_title' => 'All Products',
                'row1' => $this->fetchProductsByOrderedIds($newestIds, 12),
                'row2_title' => 'Top Rated Products',
                'row2' => $this->fetchProductsByOrderedIds($topRatedIds, 12),
                'row3_title' => 'Most Viewed Products',
                'row3' => $this->fetchProductsByOrderedIds($topViewedIds, 12),
            ];

            $featuredProductIds = collect(['row1', 'row2', 'row3'])
                ->flatMap(fn ($rowKey) => ($featuredRows[$rowKey] ?? collect())->pluck('id'))
                ->unique()
                ->values()
                ->all();

            if (! empty($featuredProductIds)) {
                $query->whereNotIn('id', $featuredProductIds);
            }
        }

        $products = $query->paginate(24)->withQueryString();
        if (! empty($featuredRows) && $products->isEmpty()) {
            $fallbackQuery = Product::with(['category', 'media', 'description'])
                ->where('stock', '>', 0);

            if ($request->has('search') && ! empty($request->search)) {
                $search = $request->search;
                $fallbackQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhereHas('description', function ($desc) use ($search) {
                            $desc->where('description', 'like', '%'.$search.'%')
                                ->orWhere('specifications', 'like', '%'.$search.'%')
                                ->orWhere('details', 'like', '%'.$search.'%');
                        });
                });
            }

            if ($request->has('category') && ! empty($request->category)) {
                $fallbackQuery->where('category_id', (int) $request->category);
            }

            if ($request->has('rating') && ! empty($request->rating)) {
                $fallbackQuery->where('rate', '>=', $request->rating);
            }

            if ($request->has('on_sale') && $request->on_sale == '1') {
                $fallbackQuery->where('discount', '>', 0);
            }

            $fallbackQuery->orderByDesc('created_at');
            $products = $fallbackQuery->paginate(24)->withQueryString();
        }

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }])->orderBy('name')->get();

        $aiRecommendations = [];
        $hasOrderHistory = false;

        if (Auth::check()) {
            try {
                $ai = app(HybridRecommendationService::class);
                $userHistory = $this->getUserInteractionProductIds(8);
                if (! empty($userHistory)) {
                    $aiRecommendations = $ai->recommendForCart($userHistory, 8);
                }
                if (empty($aiRecommendations)) {
                    $aiRecommendations = $ai->fallbackGeneral(8);
                }
                $hasOrderHistory = \Illuminate\Support\Facades\DB::table('orders')
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['completed', 'delivered'])
                    ->exists();
            } catch (\Throwable $e) {
                \Log::debug('Recommendation service error on shop page', ['error' => $e->getMessage()]);
            }
        }

        return view('shop', compact('products', 'categories', 'featuredRows', 'aiRecommendations', 'hasOrderHistory'));
    }

    public function show(string $publicId, string $slug)
    {
        $product = $this->resolveShopProduct($publicId, ['category', 'media', 'description', 'ratings.user']);
        if ($slug !== $product->slug) {
            return redirect()->route('shop.show', [
                'public_id' => $product->public_id,
                'slug' => $product->slug,
            ], 301);
        }
        // Increment view count
        $product->increment('views');

        // Get related products from same category (basic fallback)
        $relatedProducts = Product::with(['category', 'media', 'description'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('stock', '>', 0)
            ->take(4)
            ->get();

        // Get complementary products (frequently bought together)
        $complementaryProducts = collect();

        // Recommendations: Apriori + complementary products for logged-in users with orders
        $aiRecommendations = [];
        $hasOrderHistory = false;
        try {
            $ai = app(HybridRecommendationService::class);
            $userHistory = $this->getUserInteractionProductIds(8);
            if (! empty($userHistory)) {
                $aiRecommendations = $ai->recommendForCart($userHistory, 8);
            }
            if (empty($aiRecommendations)) {
                $aiRecommendations = $ai->recommend($product->id, 8);
            }
            if (empty($aiRecommendations)) {
                $aiRecommendations = $ai->fallbackGeneral(8);
            }
            // For logged-in users with completed orders, get complementary products
            if (Auth::check()) {
                $hasOrderHistory = \Illuminate\Support\Facades\DB::table('orders')
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['completed', 'delivered'])
                    ->exists();
                if ($hasOrderHistory) {
                    $complementaryProducts = $product->complementaryProducts(4);
                }
            }
        } catch (\Throwable $e) {
            \Log::debug('Recommendation service error on product page', ['error' => $e->getMessage()]);
        }

        return view('shop.show', compact('product', 'relatedProducts', 'aiRecommendations', 'hasOrderHistory', 'complementaryProducts'));
    }

    public function trackViewActivity(Request $request, string $publicId, string $slug)
    {
        $request->validate([
            'event' => 'nullable|string|in:view_start,heartbeat,view_end',
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
        ]);

        $product = $this->resolveShopProduct($publicId, [], ['id']);
        $duration = (int) ($request->integer('duration_seconds', 0));
        $event = $request->input('event', 'heartbeat');
        $sessionId = $request->session()->getId();

        if ($duration < self::MIN_VIEW_SECONDS_TO_RECORD) {
            return response()->json(['success' => true, 'skipped' => true]);
        }

        $existing = ProductView::where('product_id', $product->id)
            ->where('session_id', $sessionId)
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->first();

        if (! $existing) {
            ProductView::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'view_count' => in_array($event, ['view_start', 'view_end'], true) ? 1 : 0,
                'viewed_seconds' => $duration,
                'last_activity_at' => now(),
            ]);
        } else {
            $existing->update([
                'user_id' => $existing->user_id ?: Auth::id(),
                'view_count' => $existing->view_count + (in_array($event, ['view_start', 'view_end'], true) ? 1 : 0),
                'viewed_seconds' => max($existing->viewed_seconds, $duration),
                'last_activity_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function categories(Request $request)
    {
        $query = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }]);

        // Search
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $categories = $query->orderBy('name')->get();

        return view('categories', compact('categories'));
    }

    public function category(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = Product::with(['category', 'media', 'description'])
            ->where('category_id', $category->id)
            ->where('stock', '>', 0);

        // Search within category
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhereHas('description', function ($desc) use ($search) {
                        $desc->where('description', 'like', '%'.$search.'%')
                            ->orWhere('specifications', 'like', '%'.$search.'%')
                            ->orWhere('details', 'like', '%'.$search.'%');
                    });
            });
        }

        // Rating filter
        if ($request->has('rating') && ! empty($request->rating)) {
            $query->where('rate', '>=', $request->rating);
        }

        // Discount filter
        if ($request->has('on_sale') && $request->on_sale == '1') {
            $query->where('discount', '>', 0);
        }

        // Sorting
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order;

            if (in_array($sortBy, ['name', 'new_price', 'created_at', 'rate'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(24)->withQueryString();

        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock', '>', 0);
        }])->orderBy('name')->get();

        return view('category', compact('category', 'products', 'categories'));
    }

    public function storeRating(Request $request, string $publicId, string $slug)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You must be logged in to rate products.'], 401);
            }

            return redirect()->back()->with('error', 'You must be logged in to rate products.');
        }

        $product = $this->resolveShopProduct($publicId);

        // Check if user already rated this product
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existingRating) {
            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
        } else {
            Rating::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
        }

        // Update product's average rating
        $averageRating = $product->ratings()->avg('rating');
        $product->update(['rate' => $averageRating]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Thank you for your rating!']);
        }

        return back()->with('success', 'Thank you for your rating!');
    }

    private function resolveShopProduct(string $publicId, array $with = [], array $select = ['*']): Product
    {
        $query = Product::query()->with($with)->select($select);

        return $query->where('public_id', $publicId)->firstOrFail();
    }

    /**
     * Get product IDs the user has interacted with (recently viewed).
     * Used to build personalized AI recommendations on the shop home.
     */
    private function getUserInteractionProductIds(int $limit = 8): array
    {
        $userId = Auth::id();
        $sessionId = session()->getId();
        // 1) Authenticated user order history
        if ($userId) {
            $purchased = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.user_id', $userId)
                ->whereIn('orders.status', ['completed', 'delivered'])
                ->orderByDesc('orders.created_at')
                ->limit($limit)
                ->pluck('order_items.product_id')
                ->unique()
                ->values()
                ->all();
            if (! empty($purchased)) {
                return $purchased;
            }
        }
        // 2) Recently viewed products (by user or session)
        $viewQuery = ProductView::query()
            ->select('product_id')
            ->orderByDesc('last_activity_at')
            ->limit($limit)
            ->distinct();
        if ($userId) {
            $viewQuery->where(function ($q) use ($userId, $sessionId) {
                $q->where('user_id', $userId)->orWhere('session_id', $sessionId);
            });
        } else {
            $viewQuery->where('session_id', $sessionId);
        }
        $viewed = $viewQuery->pluck('product_id')->unique()->values()->all();
        if (! empty($viewed)) {
            return $viewed;
        }
        // 3) Cart items (still useful even if not purchased)
        $cartQuery = DB::table('cart_items')
            ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
            ->orderByDesc('cart_items.created_at')
            ->limit($limit)
            ->distinct();
        if ($userId) {
            $cartQuery->where('carts.user_id', $userId);
        } else {
            $cartQuery->where('carts.session_id', $sessionId);
        }
        $cart = $cartQuery->pluck('cart_items.product_id')->unique()->values()->all();

        return $cart;
    }

    private function topRatedProductIds(int $limit = 16): array
    {
        return Product::where('stock', '>', 0)
            ->where('rate', '>', 0)
            ->orderByDesc('rate')
            ->orderByDesc('views')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('id')
            ->all();
    }

    private function topViewedProductIds(int $limit = 16, array $excludeIds = []): array
    {
        $query = Product::query()
            ->leftJoin('product_views', 'products.id', '=', 'product_views.product_id')
            ->where('products.stock', '>', 0)
            ->when(! empty($excludeIds), function ($q) use ($excludeIds) {
                $q->whereNotIn('products.id', $excludeIds);
            })
            ->groupBy('products.id', 'products.views', 'products.created_at')
            ->orderByRaw('SUM(COALESCE(product_views.view_count,0) + COALESCE(product_views.viewed_seconds,0) / 30) DESC')
            ->orderByDesc('products.views')
            ->orderByDesc('products.created_at')
            ->limit($limit);

        return $query->pluck('products.id')->all();
    }

    private function zigZagIds(array $left, array $right, int $limit = 16): array
    {
        $result = [];
        $left = array_values($left);
        $right = array_values($right);
        $max = max(count($left), count($right));

        for ($i = 0; $i < $max; $i++) {
            if (isset($left[$i]) && ! in_array($left[$i], $result, true)) {
                $result[] = $left[$i];
            }
            if (isset($right[$i]) && ! in_array($right[$i], $result, true)) {
                $result[] = $right[$i];
            }
            if (count($result) >= $limit) {
                break;
            }
        }

        return array_slice($result, 0, $limit);
    }

    private function discoverMoreIds(array $excludeIds, int $limit = 24): array
    {
        $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
        $query = Product::where('stock', '>', 0)->orderByDesc('created_at');

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $candidateIds = $query->limit(max($limit * 6, 96))->pluck('id')->all();

        return array_slice($this->stableShuffleIds($candidateIds, 'discover_more'), 0, $limit);
    }

    private function fetchProductsByOrderedIds(array $ids, int $limit = 8)
    {
        $ids = array_values(array_unique(array_map('intval', array_slice($ids, 0, $limit))));

        if (empty($ids)) {
            return collect();
        }

        $orderCase = collect($ids)
            ->values()
            ->map(fn ($id, $index) => "WHEN {$id} THEN {$index}")
            ->implode(' ');

        return Product::with(['category', 'media', 'description'])
            ->whereIn('id', $ids)
            ->orderByRaw("CASE id {$orderCase} END")
            ->get();
    }

    private function stableShuffleIds(array $ids, string $bucket): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (count($ids) < 2) {
            return $ids;
        }

        $window = (int) floor(now()->timestamp / (self::FEATURE_REFRESH_HOURS * 3600));
        $seed = $bucket.':'.$window;

        usort($ids, function ($a, $b) use ($seed) {
            $ha = crc32($seed.':'.$a);
            $hb = crc32($seed.':'.$b);

            return $ha <=> $hb;
        });

        return $ids;
    }
}
