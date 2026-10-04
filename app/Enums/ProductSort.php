<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

/**
 * Allow-listed catalog sort options.
 *
 * Mapping user input to this enum (instead of interpolating it into an
 * ORDER BY clause) is what keeps the sort parameter injection safe.
 */
enum ProductSort: string
{
    case Newest = 'newest';
    case PriceLowToHigh = 'price_asc';
    case PriceHighToLow = 'price_desc';
    case NameAscending = 'name';

    public function label(): string
    {
        return match ($this) {
            self::Newest => 'Terbaru',
            self::PriceLowToHigh => 'Harga terendah',
            self::PriceHighToLow => 'Harga tertinggi',
            self::NameAscending => 'Nama A-Z',
        };
    }

    /**
     * Every ordering ends with a unique tie-breaker so pagination is stable.
     */
    public function apply(Builder $query): Builder
    {
        return match ($this) {
            self::Newest => $query->orderByDesc('created_at')->orderByDesc('id'),
            self::PriceLowToHigh => $query->orderBy('price')->orderByDesc('id'),
            self::PriceHighToLow => $query->orderByDesc('price')->orderByDesc('id'),
            self::NameAscending => $query->orderBy('name')->orderByDesc('id'),
        };
    }

    /**
     * Unknown or missing values fall back to the default instead of erroring.
     */
    public static function fromRequest(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Newest;
    }
}
