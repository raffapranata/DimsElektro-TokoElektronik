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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // A category cannot be removed while it still owns products.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('brand', 80);
            // Rupiah has no sub-unit in practice, but decimal keeps the schema
            // honest for any future currency or fractional-price requirement.
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('warranty_months')->default(0);
            $table->json('specs')->nullable();
            $table->string('image')->nullable();
            // Products are deactivated rather than deleted once they have
            // historical order items.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('brand');
            // Catalog listing: active products, optionally narrowed to a
            // category, ordered by date.
            $table->index(['is_active', 'category_id']);
            $table->index(['is_active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
