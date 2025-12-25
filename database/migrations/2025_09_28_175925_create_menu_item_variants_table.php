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
        Schema::create('menu_item_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->decimal('price', 10, 2);
            $t->boolean('is_active')->default(true);
            $t->string('sku')->nullable();
            $t->unsignedInteger('position')->default(0);

            $t->enum('discount_type', ['percent','fixed'])->nullable();
            $t->decimal('discount_value', 10, 2)->nullable();
            $t->timestamp('discount_starts_at')->nullable();
            $t->timestamp('discount_ends_at')->nullable();

            $t->softDeletes();
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_variants');
    }
};
