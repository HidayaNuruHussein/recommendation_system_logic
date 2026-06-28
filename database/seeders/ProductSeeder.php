<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Seed products (minimal safe seeding).
     */
    public function run(): void
    {
        // Hakikisha kuna category angalau moja
        $category = Category::first();
        
        if (!$category) {
            // Ikiwa hakuna category, unda moja ya default
            $category = Category::create([
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Bidhaa za jumla',
                'public_id' => (string) Str::uuid(),
            ]);
        }

        // Unda sample product ikiwa hakuna products
        if (Product::query()->count() === 0) {
            Product::create([
                'name' => 'Sample Product',
                'slug' => 'sample-product',
                'category_id' => $category->id, // 🔥 HAPA NDIO KITU MUHIMU
                'public_id' => (string) Str::uuid(),
                'old_price' => 1200.00,
                'new_price' => 1000.00,
                'discount' => 17,
                'rate' => 4,
                'stock' => 10,
                'thumbnail' => 'sample.jpg',
                'is_advertised' => false,
            ]);

            // Unda product nyingine za sample ikiwa unataka
            Product::create([
                'name' => 'Smartphone X',
                'slug' => 'smartphone-x',
                'category_id' => $category->id,
                'public_id' => (string) Str::uuid(),
                'old_price' => 850.00,
                'new_price' => 750.00,
                'discount' => 12,
                'rate' => 5,
                'stock' => 25,
                'thumbnail' => 'phone-x.jpg',
                'is_advertised' => true,
            ]);

            Product::create([
                'name' => 'Laptop Pro',
                'slug' => 'laptop-pro',
                'category_id' => $category->id,
                'public_id' => (string) Str::uuid(),
                'old_price' => 1500.00,
                'new_price' => 1350.00,
                'discount' => 10,
                'rate' => 4,
                'stock' => 5,
                'thumbnail' => 'laptop-pro.jpg',
                'is_advertised' => false,
            ]);
        }
    }
}