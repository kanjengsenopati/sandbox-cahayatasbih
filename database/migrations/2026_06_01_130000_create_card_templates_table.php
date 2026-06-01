<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_year_id')->nullable();
            $table->string('name');
            $table->enum('type', ['student_card', 'exam_card']);
            $table->string('background_image')->nullable();
            $table->json('layout')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('exam_bill_requirements')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
        });

        // Migrasikan layout default lama ke template perdana jika ada
        try {
            $oldSetting = DB::table('application_settings')->first();
            if ($oldSetting && ($oldSetting->student_card_layout || $oldSetting->student_card_image)) {
                $activeYear = DB::table('academic_years')->where('is_active', true)->first();
                DB::table('card_templates')->insert([
                    'id' => Str::uuid()->toString(),
                    'name' => 'Template Kartu Santri Default',
                    'type' => 'student_card',
                    'background_image' => $oldSetting->student_card_image,
                    'layout' => $oldSetting->student_card_layout,
                    'is_active' => true,
                    'academic_year_id' => $activeYear?->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Abaikan jika database belum siap sepenuhnya atau settings kosong
            Log::warning('Gagal memigrasi desain kartu santri lama: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_templates');
    }
};
