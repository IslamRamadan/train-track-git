<?php

namespace App\Services\DatabaseServices;

use App\Models\Coach;

class DB_Coaches
{


    public function create_coach(mixed $gym, mixed $speciality, mixed $certificates, mixed $user_id, $package_id = 5)
    {
        return Coach::query()->create([
            'gym' => $gym,
            'speciality' => $speciality,
            'certificates' => $certificates,
            'user_id' => $user_id,
            'package_id' => $package_id,
        ]);
    }

    public function get_coach_info(mixed $coach_id)
    {
        return Coach::query()->with('package')->where('user_id', $coach_id)->first();
    }

    public function update_coach(mixed $coach_id, $gym, $speciality, $certificates)
    {
        return Coach::query()->where('user_id', $coach_id)->update([
            'gym' => $gym,
            'speciality' => $speciality,
            'certificates' => $certificates,
        ]);
    }

    public function change_coach_status($id, mixed $status)
    {
        return Coach::query()->where('user_id', $id)->update(['status' => $status]);
    }

    public function update_coach_package(mixed $coach_id, $package_id)
    {
        return Coach::query()->where('user_id', $coach_id)->update([
            'package_id' => $package_id
        ]);
    }
    public function update_coach_data(mixed $coach_id, $data)
    {
        return Coach::query()->where('user_id', $coach_id)->update($data);
    }
}
