<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Stock integrity under concurrency.
 *
 * These tests need a real MySQL/MariaDB server: SQLite silently ignores
 * SELECT ... FOR UPDATE, which would make them pass without proving anything.
 */
class StockConcurrencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validShipping(): array
    {
        return [
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Merdeka No. 10, Bandung, Jawa Barat',
        ];
    }

    public function test_a_competing_writer_is_blocked_while_the_row_lock_is_held(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        // A second, independent connection stands in for a concurrent request.
        config()->set('database.connections.concurrent_probe', config('database.connections.mysql'));
        DB::purge('concurrent_probe');

        $probe = DB::connection('concurrent_probe');
        // Fail fast instead of waiting the default 50 seconds.
        $probe->statement('SET SESSION innodb_lock_wait_timeout = 1');

        // The application connection holds the row lock, exactly like
        // CheckoutService does for the duration of its transaction.
        Product::query()->whereKey($product->id)->lockForUpdate()->first();

        $startedAt = microtime(true);

        try {
            $probe->table('products')->where('id', $product->id)->lockForUpdate()->first();
            $this->fail('The competing writer should have been blocked by the row lock.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Lock wait timeout', $exception->getMessage());
        } finally {
            DB::purge('concurrent_probe');
        }

        // The probe waited for the lock rather than reading immediately.
        $this->assertGreaterThan(0.9, microtime(true) - $startedAt);
    }

    public function test_the_last_units_cannot_be_oversold_by_two_checkouts(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'price' => 100_000]);
        $firstCustomer = User::factory()->customer()->create();
        $secondCustomer = User::factory()->customer()->create();

        $this->actingAs($firstCustomer)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), $this->validShipping())
            ->assertRedirect();

        $this->assertSame(0, $product->refresh()->stock);

        $response = $this->actingAs($secondCustomer)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->validShipping());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Only the first order exists, and stock never went negative.
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, $product->refresh()->stock);
    }

    public function test_stock_never_goes_negative_across_many_checkouts(): void
    {
        $product = Product::factory()->create(['stock' => 3, 'price' => 100_000]);

        $outcomes = [];

        foreach (range(1, 6) as $ignored) {
            $customer = User::factory()->customer()->create();

            $response = $this->actingAs($customer)
                ->withSession(['cart' => [$product->id => 1]])
                ->post(route('checkout.store'), $this->validShipping());

            $outcomes[] = $response->isRedirect(route('cart.index')) ? 'rejected' : 'accepted';
        }

        $accepted = count(array_filter($outcomes, fn (string $outcome): bool => $outcome === 'accepted'));

        // Six customers compete for three units: exactly three win.
        $this->assertSame(3, $accepted);
        $this->assertSame(0, $product->refresh()->stock);
        $this->assertDatabaseCount('orders', 3);
        $this->assertDatabaseCount('order_items', 3);
    }
}
