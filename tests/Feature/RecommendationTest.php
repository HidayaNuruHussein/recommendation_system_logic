<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_endpoint_returns_products_for_product_with_bought_together_history(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $productA = Product::create([
            'category_id' => $category->id,
            'name' => 'Product A',
            'slug' => 'product-a',
            'new_price' => 500,
            'stock' => 10,
        ]);

        $productB = Product::create([
            'category_id' => $category->id,
            'name' => 'Product B',
            'slug' => 'product-b',
            'new_price' => 600,
            'stock' => 10,
        ]);

        $productC = Product::create([
            'category_id' => $category->id,
            'name' => 'Product C',
            'slug' => 'product-c',
            'new_price' => 700,
            'stock' => 10,
        ]);

        $order1 = Order::create([
            'order_number' => 'ORD-TEST-1',
            'user_id' => $user->id,
            'status' => 'completed',
            'subtotal' => 1100,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total_amount' => 1100,
            'currency' => 'TZS',
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $productA->id,
            'product_name' => $productA->name,
            'quantity' => 1,
            'unit_price' => 500,
            'total_price' => 500,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $productB->id,
            'product_name' => $productB->name,
            'quantity' => 1,
            'unit_price' => 600,
            'total_price' => 600,
        ]);

        $response = $this->getJson('/api/recommendations/'.$productA->id);

        $response->assertStatus(200);
        $response->assertJsonPath('recommendations.0.id', $productB->id);
    }
}
