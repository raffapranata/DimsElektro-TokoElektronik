<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Keeps a unique, human readable slug in sync with the model's name.
 *
 * The slug is generated on create. On update it is only regenerated when the
 * source column changed and the slug was not set explicitly, so published URLs
 * stay stable.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function (self $model): void {
            if ($model->isDirty('slug')) {
                return;
            }

            if ($model->isDirty($model->slugSourceColumn())) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    /**
     * Column the slug is derived from.
     */
    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    protected function generateUniqueSlug(): string
    {
        $base = Str::slug((string) $this->{$this->slugSourceColumn()});
        $base = $base !== '' ? $base : 'item';

        $slug = $base;
        $suffix = 2;

        while ($this->slugAlreadyTaken($slug)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugAlreadyTaken(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
