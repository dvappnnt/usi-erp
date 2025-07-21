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
        Schema::create('abstract_of_canvass_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abstract_of_canvass_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id');
            $table->foreignId('supplier_id');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abstract_of_canvass_items');
    }
};
