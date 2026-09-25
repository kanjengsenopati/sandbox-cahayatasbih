<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $auditMenu = MenuNavigation::where('name', 'like', '%Audit%')->first();
        if ($auditMenu) {
            SubMenuNavigation::updateOrCreate(
                ['name' => 'Tagihan Pembayaran'],
                [
                    'id' => Str::uuid(),
                    'menu_navigation_id' => $auditMenu->id,
                    'name' => 'Tagihan Pembayaran',
                    'url' => 'admin/audit/tagihan-pembayaran',
                    'order' => 1,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SubMenuNavigation::where('name', 'Tagihan Pembayaran')->delete();
    }
};
