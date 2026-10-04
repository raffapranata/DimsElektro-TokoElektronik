<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'brand',
    'price',
    'stock',
    'warranty_months',
    'specs',
    'image',
    'is_active',
])]
class Product extends Model
{
    use HasFactory, HasSlug;

    /**
     * Disk used for product images so the view code has a single source.
     */
    public const string IMAGE_DISK = 'public';

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'warranty_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        // Escape LIKE wildcards so user input is matched literally.
        $escaped = addcslashes($term, '%_\\');

        return $query->where(function (Builder $query) use ($escaped): void {
            $query->where('name', 'like', "%{$escaped}%")
                ->orWhere('brand', 'like', "%{$escaped}%");
        });
    }

    #[Scope]
    protected function inCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (blank($categorySlug)) {
            return $query;
        }

        return $query->whereHas('category', function (Builder $query) use ($categorySlug): void {
            $query->where('slug', $categorySlug);
        });
    }

    #[Scope]
    protected function byBrand(Builder $query, ?string $brand): Builder
    {
        if (blank($brand)) {
            return $query;
        }

        return $query->where('brand', $brand);
    }

    #[Scope]
    protected function lowStock(Builder $query, int $threshold): Builder
    {
        return $query->where('stock', '<=', $threshold);
    }

    /**
     * Filter the admin list by stock state: "low", "out", "available".
     */
    #[Scope]
    protected function withStockState(Builder $query, ?string $state, int $lowStockThreshold): Builder
    {
        return match ($state) {
            'low' => $query->where('stock', '<=', $lowStockThreshold),
            'out' => $query->where('stock', '<=', 0),
            'available' => $query->where('stock', '>', 0),
            default => $query,
        };
    }

    /**
     * Filter the admin list by visibility: "active" or "inactive".
     */
    #[Scope]
    protected function withActiveState(Builder $query, ?string $state): Builder
    {
        return match ($state) {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            default => $query,
        };
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Absolute URL of the uploaded image, or null when none was uploaded.
     */
    public function imageUrl(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        return Storage::disk(self::IMAGE_DISK)->url($this->image);
    }

    /**
     * Distinct brands currently present, used to build catalog filter options.
     *
     * @return array<int, string>
     */
    public static function availableBrands(): array
    {
        return static::query()
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->all();
    }

    /**
     * Subtotal for a given quantity, always derived from the stored price.
     */
    public function subtotalFor(int $quantity): float
    {
        return round((float) $this->price * $quantity, 2);
    }
}
