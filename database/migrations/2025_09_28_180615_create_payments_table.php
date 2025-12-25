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
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('method');        // cash|card|wallet|khqr...
            $t->string('status')->default('pending'); // pending|confirmed|failed|refunded
            $t->string('transaction_id')->nullable();
            $t->timestamp('confirmed_at')->nullable();

            $t->string('currency', 3);  // 'KHR'|'USD'
            $t->bigInteger('amount_khr')->default(0);
            $t->bigInteger('tendered_khr')->nullable();
            $t->bigInteger('change_khr')->nullable();
            $t->decimal('exchange_rate', 12, 4)->default(0);
            $t->json('meta')->nullable();

            $t->timestamps();
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
