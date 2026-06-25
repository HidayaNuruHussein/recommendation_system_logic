<?php

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('product:assign-media', function () {
    $keywordMap = [
        'phone' => 'products/phone.jpg',
        'laptop' => 'products/laptop.jpg',
        'shoe' => 'products/shoes.jpg',
    ];

    $defaultPath = 'products/default.jpg';
    $assigned = 0;

    Product::with('media')->chunkById(100, function ($products) use (&$assigned, $keywordMap, $defaultPath) {
        foreach ($products as $product) {
            if ($product->media->where('type', 'image')->isNotEmpty()) {
                continue;
            }

            $matchedPath = $defaultPath;
            $lowerName = Str::lower($product->name);

            foreach ($keywordMap as $keyword => $path) {
                if (Str::contains($lowerName, $keyword)) {
                    $matchedPath = $path;
                    break;
                }
            }

            if (!Storage::exists('public/' . $matchedPath)) {
                $matchedPath = $defaultPath;
            }

            ProductMedia::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'image',
                    'file_path' => $matchedPath,
                ],
                [
                    'is_primary' => true,
                ]
            );

            $assigned++;
        }
    });

    $this->info("Assigned media to {$assigned} product(s).");
})->purpose('Assign default product media for products missing image media');
