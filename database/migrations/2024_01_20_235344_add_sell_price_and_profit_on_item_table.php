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
        Schema::table('items', function (Blueprint $table) {
            $table->bigInteger('selling_price')->nullable();
            $table->bigInteger('profit')->nullable();
            $table->bigInteger('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('selling_price');
            $table->dropColumn('profit');
            $table->bigInteger('price')->nullable(false)->change();
        });
    }
};
