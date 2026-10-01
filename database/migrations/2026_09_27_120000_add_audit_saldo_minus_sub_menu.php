<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mainMenu = MenuNavigation::where('name', 'Audit dan Sinkron')->first();
        if ($mainMenu) {
            $existing = SubMenuNavigation::where('url', '/admin/audit/saldo-minus')
                ->orWhere('name', 'Audit Saldo Minus')
                ->first();

            if (!$existing) {
                SubMenuNavigation::create([
                    'id' => (string) Str::uuid(),
                    'menu_navigation_id' => $mainMenu->id,
                    'name' => 'Audit Saldo Minus',
                    'url' => '/admin/audit/saldo-minus',
                    'permission' => 'Manage Audit dan Sinkron',
                    'order' => 4,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SubMenuNavigation::where('url', '/admin/audit/saldo-minus')
            ->orWhere('name', 'Audit Saldo Minus')
            ->delete();
    }
};
