<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(500_000, 25_000_000);

        return [
            'user_id' => User::factory()->customer(),
            'order_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'status' => OrderStatus::Pending,
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->numerify('08##########'),
            'shipping_address' => fake()->streetAddress().', '.fake()->city().', Indonesia',
            'notes' => null,
            'subtotal' => $subtotal,
            'shipping_cost' => 0,
            'total_amount' => $subtotal,
        ];
    }

    public function withStatus(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function pending(): static
    {
        return $this->withStatus(OrderStatus::Pending);
    }

    public function processing(): static
    {
        return $this->withStatus(OrderStatus::Processing);
    }

    public function shipped(): static
    {
        return $this->withStatus(OrderStatus::Shipped);
    }

    public function completed(): static
    {
        return $this->withStatus(OrderStatus::Completed);
    }

    public function cancelled(): static
    {
        return $this->withStatus(OrderStatus::Cancelled);
    }
}
