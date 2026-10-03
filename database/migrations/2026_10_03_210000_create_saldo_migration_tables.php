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
        if (!Schema::hasTable('saldo_migration_batches')) {
            Schema::create('saldo_migration_batches', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('migration_token')->nullable();
                $table->string('sent_by')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->integer('total_students')->default(0);
                $table->bigInteger('total_saldo')->default(0);
                $table->bigInteger('total_saving')->default(0);
                $table->string('status')->default('PENDING'); // PENDING, APPLIED, REJECTED
                $table->timestamp('applied_at')->nullable();
                $table->string('applied_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saldo_migration_items')) {
            Schema::create('saldo_migration_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('batch_id')->index();
                $table->uuid('student_id')->index();
                $table->string('nis')->nullable();
                $table->string('nisn')->nullable();
                $table->string('name');
                $table->string('classroom')->nullable();
                $table->bigInteger('old_saldo')->default(0);
                $table->bigInteger('old_saving')->default(0);
                $table->bigInteger('current_local_saldo')->default(0);
                $table->bigInteger('diff_saldo')->default(0);
                $table->string('status')->default('PENDING');
                $table->timestamps();

                $table->foreign('batch_id')->references('id')->on('saldo_migration_batches')->onDelete('cascade');
            });
        }

        // Daftarkan sub menu di sidebar Audit dan Sinkron
        try {
            $mainMenu = \App\Models\MenuNavigation::where('name', 'Audit dan Sinkron')->first();
            if ($mainMenu) {
                $existing = \App\Models\SubMenuNavigation::where('url', '/admin/migration-saldo')
                    ->orWhere('name', 'Konfirmasi Migrasi Saldo')
                    ->first();

                if (!$existing) {
                    \App\Models\SubMenuNavigation::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'menu_navigation_id' => $mainMenu->id,
                        'name' => 'Konfirmasi Migrasi Saldo',
                        'url' => '/admin/migration-saldo',
                        'permission' => 'Manage Audit dan Sinkron',
                        'order' => 5,
                        'is_active' => true,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if MenuNavigation table doesn't exist yet
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_migration_items');
        Schema::dropIfExists('saldo_migration_batches');
    }
};
