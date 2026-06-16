<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename 'Asatidz' role to 'Penanggung Jawab'
        DB::table('roles')->where('name', 'Asatidz')->update(['name' => 'Penanggung Jawab']);

        // Clear Spatie permission cache
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // Silence if class not found or cache clearing fails
        }

        // 2. Update notification templates
        DB::table('notification_templates')
            ->where('body_template', 'like', '%Ustadz%')
            ->get()
            ->each(function ($template) {
                $newBody = str_replace('Ustadz', 'Penanggung Jawab', $template->body_template);
                DB::table('notification_templates')
                    ->where('id', $template->id)
                    ->update(['body_template' => $newBody]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename back 'Penanggung Jawab' to 'Asatidz'
        DB::table('roles')->where('name', 'Penanggung Jawab')->update(['name' => 'Asatidz']);

        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // Silence
        }

        // 2. Revert notification templates
        DB::table('notification_templates')
            ->where('body_template', 'like', '%Penanggung Jawab%')
            ->get()
            ->each(function ($template) {
                $newBody = str_replace('Penanggung Jawab', 'Ustadz', $template->body_template);
                DB::table('notification_templates')
                    ->where('id', $template->id)
                    ->update(['body_template' => $newBody]);
            });
    }
};
