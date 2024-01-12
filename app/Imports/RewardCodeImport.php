<?php

namespace App\Imports;
use Illuminate\Support\Collection;
use App\Models\RewardItem;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RewardCodeImport implements ToCollection
{
    public function collection(Collection $collection)
    {

          foreach($collection  as $key=> $row){
            if ($key > 2 && !empty($row[1])) {
                RewardItem::updateOrCreate([
                    'code' =>  $row[1]
                ]
                ,[
                    'code' => $row[1],
                    'expired_at'=> Carbon::createFromDate(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])),
                    'reward_id' => request()->reward_id,
                    'status' => RewardItem::STATUS_ACTIVE
                ]);
            }
          }
    }
}
