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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // One payment record per order, and payment history must outlive
            // the order, so deleting an order is restricted while one exists.
            // The unique index also satisfies the foreign key index
            // requirement, so no duplicate index is declared.
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->unique('order_id');

            // Identifier returned by the payment gateway (Midtrans
            // transaction_id, manual transfer reference, ...).
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('payment_type', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            // Raw gateway payload, kept for auditing. Never contains secrets.
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
