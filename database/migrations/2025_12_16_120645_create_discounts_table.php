<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable()->unique(); // e.g. "HAPPY10"
            $table->enum('type', ['fixed_khr', 'percent']); // fixed in KHR or %
            $table->decimal('value', 12, 2); // percent: 10.00 | fixed_khr: 2000.00

            $table->unsignedBigInteger('min_subtotal_khr')->nullable();  // apply only if subtotal >= this
            $table->unsignedBigInteger('max_discount_khr')->nullable();  // cap discount

            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedInteger('usage_limit')->nullable(); // total uses allowed
            $table->unsignedInteger('used_count')->default(0);

            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
