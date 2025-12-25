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
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('order_code')->unique();
            $t->string('status')->default('pending'); // pending|paid|cancelled|refunded
            $t->timestamp('paid_at')->nullable();

            // money in KHR (riels, integers)
            $t->bigInteger('subtotal_khr')->default(0);
            $t->bigInteger('discount_khr')->default(0);
            $t->bigInteger('tax_khr')->default(0);
            $t->bigInteger('total_khr')->default(0);

            $t->unsignedInteger('total_items')->default(0);

            // rates
            $t->decimal('tax_rate', 5, 2)->default(0);
            $t->decimal('exchange_rate', 12, 4)->default(0);

            $t->timestamps();
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
