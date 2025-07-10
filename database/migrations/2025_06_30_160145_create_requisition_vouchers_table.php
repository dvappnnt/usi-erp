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
        Schema::create('requisition_vouchers', function (Blueprint $table) {
            $table->id();
            $table->text('purpose');
            $table->string('requisitioner');
            $table->string('requisitioner_position');
            $table->string('recommending_approval');
            $table->string('recommending_position');
            $table->string('approved_by');
            $table->string('approved_position');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_vouchers');
    }
};
