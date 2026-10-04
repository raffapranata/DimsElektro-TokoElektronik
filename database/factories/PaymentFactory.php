<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'transaction_id' => null,
            'payment_type' => null,
            'status' => PaymentStatus::Pending,
            'amount' => fake()->numberBetween(500_000, 25_000_000),
            'paid_at' => null,
            'raw_response' => null,
        ];
    }

    public function withStatus(PaymentStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'transaction_id' => $status === PaymentStatus::Pending
                ? null
                : 'TRX-'.Str::upper(Str::random(10)),
            'paid_at' => $status === PaymentStatus::Paid ? now() : null,
        ]);
    }

    public function paid(): static
    {
        return $this->withStatus(PaymentStatus::Paid);
    }

    public function failed(): static
    {
        return $this->withStatus(PaymentStatus::Failed);
    }

    public function expired(): static
    {
        return $this->withStatus(PaymentStatus::Expired);
    }

    public function cancelled(): static
    {
        return $this->withStatus(PaymentStatus::Cancelled);
    }

    public function refunded(): static
    {
        return $this->withStatus(PaymentStatus::Refunded);
    }
}
