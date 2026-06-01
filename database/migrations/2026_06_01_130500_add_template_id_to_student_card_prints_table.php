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
        Schema::table('student_card_prints', function (Blueprint $table) {
            $table->uuid('card_template_id')->nullable()->after('student_id');
            $table->foreign('card_template_id')->references('id')->on('card_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_card_prints', function (Blueprint $table) {
            $table->dropForeign(['card_template_id']);
            $table->dropColumn('card_template_id');
        });
    }
};
