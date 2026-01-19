<?php

namespace App\Imports;

use App\Models\CategoryItem;
use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ItemImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * Handle the collection of rows.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $rows->each(function ($row) {
            if (!empty($row['nama_barang'])) {
                $categoryItem = CategoryItem::where('code', $row['kode_kategori'])->first();

                Item::create([
                    'category_item_id' => optional($categoryItem)->id,
                    'name' => $row['nama_barang'],
                    'code' => $row['kode_barang'],
                    'price' => $row['harga_beli'],
                    'selling_price' => $row['harga_jual'],
                    'profit' => $row['harga_jual'] - $row['harga_beli'],
                    'stock' => $row['stok'] ?? 0,
                ]);
            }
        });
    }
}
