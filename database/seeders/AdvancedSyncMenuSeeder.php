<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

class AdvancedSyncMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $auditMenu = MenuNavigation::where('name', 'like', '%Audit%')->first();

        if ($auditMenu) {
            SubMenuNavigation::updateOrCreate(
                [
                    'menu_navigation_id' => $auditMenu->id,
                    'url' => '/admin/audit/advanced-sync',
                ],
                [
                    'name' => 'Advanced Sync Saldo',
                    'is_active' => 1,
                    'permission' => 'Manage Audit dan Sinkron',
                    'order' => 6,
                ]
            );
            $this->command->info('Advanced Sync Menu added successfully.');
        } else {
            $this->command->error('Audit Menu not found!');
        }
    }
}
