<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\OrderNumberGenerator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderNumberGeneratorTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_generated_numbers_follow_the_inv_date_suffix_format(): void
    {
        $generator = new OrderNumberGenerator;

        $number = $generator->generate();

        $this->assertMatchesRegularExpression(
            '/^INV-\d{8}-[A-HJ-NP-Z2-9]{6}$/',
            $number,
            "Unexpected order number format: {$number}",
        );
        $this->assertStringContainsString('INV-'.now()->format('Ymd'), $number);
    }

    public function test_the_suffix_never_contains_ambiguous_characters(): void
    {
        $generator = new OrderNumberGenerator;

        foreach (range(1, 25) as $_) {
            $number = $generator->generate();

            $suffix = substr((string) strrchr($number, '-'), 1);
            $this->assertSame('', preg_replace('/[A-HJ-NP-Z2-9]/', '', $suffix) ?? '');
        }
    }

    public function test_generated_numbers_are_unique_against_the_database(): void
    {
        $generator = new OrderNumberGenerator;

        $numbers = [];

        for ($i = 0; $i < 10; $i++) {
            $number = $generator->generate();

            $this->assertNotContains($number, $numbers);
            Order::factory()->create(['order_number' => $number]);
            $numbers[] = $number;
        }
    }

    public function test_a_number_colliding_with_the_database_is_regenerated(): void
    {
        $generator = new OrderNumberGenerator;

        $reserved = 'INV-'.now()->format('Ymd').'-AAAAAA';
        Order::factory()->create(['order_number' => $reserved]);

        $number = $generator->generate();

        $this->assertNotSame($reserved, $number);
        $this->assertDatabaseMissing('orders', ['order_number' => $number]);
    }
}
