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
        Schema::table('abstract_of_canvass_items', function (Blueprint $table) {
            $table->integer('qty')->after('product_id');
            $table->string('units')->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abstract_of_canvass_items', function (Blueprint $table) {
            $table->dropColumn(['qty', 'units']);
        });
    }
};
