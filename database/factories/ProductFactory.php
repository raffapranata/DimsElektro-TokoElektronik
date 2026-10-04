<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Brand names used when a test does not care about the specific brand.
     *
     * @var array<int, string>
     */
    public const BRANDS = [
        'Asus', 'Acer', 'Lenovo', 'HP', 'Dell', 'Samsung', 'Xiaomi',
        'Logitech', 'Epson', 'LG', 'Keychron', 'Razer',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            // The slug is derived from the name by the HasSlug trait.
            'name' => Str::title(fake()->unique()->words(3, true)),
            'brand' => fake()->randomElement(self::BRANDS),
            'price' => fake()->numberBetween(150_000, 35_000_000),
            'stock' => fake()->numberBetween(1, 40),
            'warranty_months' => fake()->randomElement([0, 3, 6, 12, 24, 36]),
            'specs' => [
                'Warna' => fake()->randomElement(['Hitam', 'Putih', 'Silver', 'Abu-abu']),
                'Berat' => fake()->numberBetween(1, 5).' kg',
            ],
            'image' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    public function lowStock(int $stock = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
