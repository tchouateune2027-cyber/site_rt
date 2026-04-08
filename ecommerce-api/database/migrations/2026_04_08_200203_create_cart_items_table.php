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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained()
            ->onDelete('cascade');
            $table->nullableMorphs('itemable'); // This will create item_id and item_type for polymorphic relation
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'itemable_id', 'itemable_type']); // Ensure a user can't have duplicate items in the cart
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
