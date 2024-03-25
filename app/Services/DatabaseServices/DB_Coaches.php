<?php

namespace App\Services\DatabaseServices;

use App\Models\Coach;
use App\Models\User;

class DB_Coaches
{


    public function create_coach(mixed $gym, mixed $speciality, mixed $certificates, mixed $user_id)
    {
        return Coach::query()->create([
            'gym' => $gym,
            'speciality' => $speciality,
            'certificates' => $certificates,
            'user_id' => $user_id,
        ]);
    }

    public function get_coach_info(mixed $coach_id)
    {
        return Coach::query()->where('user_id', $coach_id)->first();
    }

    public function update_coach(mixed $coach_id, $gym, $speciality, $certificates)
    {
        return Coach::query()->where('user_id', $coach_id)->update([
            'gym' => $gym,
            'speciality' => $speciality,
            'certificates' => $certificates,
        ]);
    }
}
