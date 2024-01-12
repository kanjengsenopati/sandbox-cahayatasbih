<?php

namespace App\Services;


class UserServices
{
    public static function totalRange($user)
    {
        return $user->history_runs->sum('range');
    }

    public static function totalStep($user)
    {
        return $user->history_runs->sum('step');
    }

    public static function totalCalories($user)
    {
        return $user->history_runs->sum('calory');
    }
}
