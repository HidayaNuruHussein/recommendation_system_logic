<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Product 4 details:\n";
$p4 = \App\Models\Product::find(4);
if ($p4) echo " - Name: " . $p4->name . "\n";

echo "\nAll products ordered together with product 4:\n";
$complementary = $p4->complementaryProducts(4);
foreach ($complementary as $p) {
    echo " - ID {$p->id}: {$p->name}\n";
}