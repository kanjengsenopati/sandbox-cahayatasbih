<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{

    public function collection()
    {
        return  User::latest()
            ->filterGreaterThanEqual('created_at', request()->start_date)
            ->filterLowerThanEqual('created_at', request()->end_date)
            ->get();
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            str_replace('+', '', $user->phone),
            $user->point ?? 0,
            $user->experience_point ?? 0,
            $user->level->name,
            Carbon::parse($user->created_at)->translatedFormat('l, d F Y h:i:s')
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'No.Telpon',
            'Point',
            'Experience Point (XP)',
            'Level',
            'Joint At'
        ];
    }
}
