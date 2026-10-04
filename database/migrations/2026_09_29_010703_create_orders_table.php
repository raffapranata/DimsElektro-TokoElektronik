<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Historical transactions must survive account removal, so the
            // user row is restricted rather than cascaded.
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 32)->unique();
            $table->string('status', 20)->default('pending');
            // Shipping details are snapshotted at checkout so a later profile
            // change never rewrites the history of a placed order.
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 25);
            $table->text('shipping_address');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();

            // Admin order list filters by status and sorts by recency.
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
