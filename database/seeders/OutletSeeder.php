<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari outlet CAHAYA MART yang sudah ada (mungkin dengan kode 'CM' atau 'CHM')
        $cahaya = \App\Models\Outlet::where('code', 'CM')->orWhere('code', 'CHM')->first();
        if ($cahaya) {
            // Update kode ke CHM jika masih CM
            if ($cahaya->code !== 'CHM') {
                $cahaya->update(['code' => 'CHM']);
            }
        } else {
            $cahaya = \App\Models\Outlet::create([
                'code' => 'CHM',
                'name' => 'CAHAYA MART',
                'address' => 'Pusat CAHAYA MART',
                'is_active' => true,
            ]);
        }

        $ctm = \App\Models\Outlet::firstOrCreate(
            ['code' => 'CTM'],
            [
                'name' => 'CT-Mart',
                'address' => 'Outlet CT-Mart',
                'is_active' => true,
            ]
        );

        // Assign existing records to CAHAYA MART if outlet_id is null
        $tables = [
            'items', 
            'stock_histories', 
            'category_items', 
            'point_of_sale_transactions', 
            'point_of_sale_carts', 
            'cash_flows',
            'admins'
        ];

        foreach ($tables as $t) {
            \Illuminate\Support\Facades\DB::table($t)->whereNull('outlet_id')->update(['outlet_id' => $cahaya->id]);
        }

        echo "Outlet seeder selesai. CAHAYA MART ID: {$cahaya->id}, CT-Mart ID: {$ctm->id}" . PHP_EOL;
    }
}
