<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string}>
     */
    private const CATEGORIES = [
        [
            'name' => 'Laptop',
            'description' => 'Laptop kerja, gaming, dan ultrabook dari berbagai merek.',
        ],
        [
            'name' => 'Monitor',
            'description' => 'Monitor kantor dan gaming dengan panel berkualitas.',
        ],
        [
            'name' => 'Smartphone',
            'description' => 'Ponsel pintar terbaru dengan garansi resmi.',
        ],
        [
            'name' => 'Keyboard',
            'description' => 'Keyboard mekanikal, wireless, dan portabel.',
        ],
        [
            'name' => 'Mouse',
            'description' => 'Mouse presisi untuk produktivitas dan gaming.',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            Category::query()->updateOrCreate(
                ['slug' => str($category['name'])->slug()->value()],
                $category,
            );
        }
    }
}
