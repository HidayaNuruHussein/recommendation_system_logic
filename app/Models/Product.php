<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'category_id',
        'name',
        'slug',
        'old_price',
        'new_price',
        'discount',
        'rate',
        'stock',
        'thumbnail',
        'is_advertised',
    ];

    protected $casts = [
        'is_advertised' => 'boolean',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function primaryMedia()
    {
        return $this->hasOne(ProductMedia::class)
            ->where('type', 'image')
            ->orderByDesc('is_primary');
    }

    public function description()
    {
        return $this->hasOne(ProductDescription::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Get products frequently bought together with this product
     * based on completed order history (co-occurrence analysis).
     *
     * Example: laptop → adapter/charger, phone → earbuds/charger.
     */
    public function complementaryProducts(int $limit = 8)
    {
        $orderedTogether = \Illuminate\Support\Facades\DB::table('order_items as oi1')
            ->join('orders as o', 'o.id', '=', 'oi1.order_id')
            ->join('order_items as oi2', function ($join) {
                $join->on('oi2.order_id', '=', 'o.id')
                    ->where('oi2.product_id', '!=', 'oi1.product_id');
            })
            ->where('oi1.product_id', $this->id)
            ->whereIn('o.status', ['completed', 'delivered'])
            ->select('oi2.product_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as co_occurrence'))
            ->groupBy('oi2.product_id')
            ->orderByDesc('co_occurrence')
            ->limit($limit)
            ->pluck('oi2.product_id')
            ->all();

        if (empty($orderedTogether)) {
            return collect();
        }

return Product::query()
             ->whereIn('id', $orderedTogether)
             ->where('id', '!=', $this->id)
             ->where('stock', '>', 0)
            ->orderByRaw(\Illuminate\Support\Facades\DB::raw('FIELD(id, '.implode(',', $orderedTogether).')'))
            ->get();
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->old_price && $this->old_price > 0) {
            return round((($this->old_price - $this->new_price) / $this->old_price) * 100);
        }

        return 0;
    }
}
