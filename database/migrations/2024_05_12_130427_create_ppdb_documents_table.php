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
        Schema::create('ppdb_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ppdb_registration_id');
            $table->text('family_card_image')->nullable();
            $table->text('birth_certificate_image')->nullable();
            $table->text('raport_image')->nullable();
            $table->text('father_identity_image')->nullable();
            $table->text('mother_identity_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
    }
};
