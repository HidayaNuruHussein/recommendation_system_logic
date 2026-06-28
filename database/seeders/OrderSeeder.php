<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('stock', '>', 0)->get();
        $customers = User::where('role', 'customer')->get();

        // Angalia kama kuna products na customers za kutosha
        if ($products->count() < 1 || $customers->isEmpty()) {
            return;
        }

        foreach ($customers->take(2) as $index => $customer) {
            // Badilisha hapa - tumia products zote zilizopo ikiwa ni chache
            $maxItems = min($products->count(), 6);
            $minItems = min($products->count(), 2);
            $itemCount = rand($minItems, $maxItems);
            
            $orderProducts = $products->random($itemCount);
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 2);
                $unitPrice = $product->new_price ?? 1000;
                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                $orderItemsData[] = [
                    'public_id' => (string) Str::uuid(),
                    'order_id' => null,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->public_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $order = Order::create([
                'public_id' => (string) Str::uuid(),
                'order_number' => 'ORD-' . now()->format('Y') . '-' . strtoupper(Str::random(6)),
                'user_id' => $customer->id,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'currency' => 'TSh',
                'ordered_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);

            $orderId = $order->id;
            foreach ($orderItemsData as &$item) {
                $item['order_id'] = $orderId;
            }
            unset($item);

            OrderItem::insert($orderItemsData);
        }
    }
}