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
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pr_id')->nullable(); // Foreign key to PR
            $table->string('rfq_no')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('date_sent')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending'); // pending, received, declined
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('pr_id')->references('id')->on('purchase_requests')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
