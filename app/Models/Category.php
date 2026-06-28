<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'description',
        'parent_id',
        'category_group',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ✅ Parent category relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // ✅ Children categories relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // ============================================
    // HELPERS
    // ============================================
    
    public function getRelatedCategoryIds(): array
    {
        $ids = [$this->id];

        if ($this->parent_id) {
            $ids[] = $this->parent_id;
        }

        $children = $this->children()->pluck('id')->toArray();
        $ids = array_merge($ids, $children);

        if ($this->parent_id) {
            $siblings = Category::where('parent_id', $this->parent_id)
                ->where('id', '!=', $this->id)
                ->pluck('id')
                ->toArray();
            $ids = array_merge($ids, $siblings);
        }

        if ($this->category_group) {
            $sameGroup = Category::where('category_group', $this->category_group)
                ->where('id', '!=', $this->id)
                ->pluck('id')
                ->toArray();
            $ids = array_merge($ids, $sameGroup);
        }

        return array_values(array_unique($ids));
    }

    public function getProximityScores(): array
    {
        $proximity = [];
        $proximity[$this->id] = 1.0;

        if ($this->parent_id) {
            $proximity[$this->parent_id] = 0.85;
        }

        if ($this->parent_id) {
            $siblings = Category::where('parent_id', $this->parent_id)
                ->where('id', '!=', $this->id)
                ->pluck('id')
                ->toArray();
            foreach ($siblings as $siblingId) {
                $proximity[$siblingId] = 0.9;
            }
        }

        $children = $this->children()->pluck('id')->toArray();
        foreach ($children as $childId) {
            $proximity[$childId] = 0.9;
        }

        if ($this->category_group) {
            $sameGroup = Category::where('category_group', $this->category_group)
                ->where('id', '!=', $this->id)
                ->pluck('id')
                ->toArray();
            foreach ($sameGroup as $groupId) {
                if (!isset($proximity[$groupId]) || $proximity[$groupId] < 0.7) {
                    $proximity[$groupId] = 0.7;
                }
            }
        }

        arsort($proximity);
        return $proximity;
    }

    // ============================================
    // BOOT METHOD
    // ============================================
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            if (empty($category->public_id)) {
                $category->public_id = (string) Str::uuid();
            }
            if (empty($category->category_group)) {
                $category->category_group = 'Other';
            }
            if (empty($category->tags)) {
                $category->tags = ['general'];
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}