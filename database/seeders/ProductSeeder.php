<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed products (minimal safe seeding).
     */
    public function run(): void
    {
        // Create a single sample product if none exist to satisfy references.
        if (Product::query()->count() === 0) {
            Product::create([
                'name' => 'Sample Product',
                'slug' => 'sample-product',
                'public_id' => (string) \Illuminate\Support\Str::uuid(),
                'price' => 1000,
                'stock' => 10,
            ]);
        }
    }
}
