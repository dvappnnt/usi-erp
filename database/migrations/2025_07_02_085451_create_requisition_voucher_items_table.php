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
        Schema::create('requisition_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_voucher_id')->constrained('requisition_vouchers')->onDelete('cascade');
            $table->string('mat_code')->nullable();
            $table->string('particular');
            $table->string('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_voucher_items');
    }
};
