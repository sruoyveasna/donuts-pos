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
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $t->foreignId('menu_item_variant_id')->nullable()->constrained('menu_item_variants')->nullOnDelete();

            $t->unsignedInteger('quantity');
            $t->decimal('price', 10, 2);    // unit price (USD style, display)
            $t->decimal('subtotal', 10, 2); // price * qty (display); keep KHR totals on order/payments
            $t->json('customizations')->nullable();
            $t->string('note')->nullable();

            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
