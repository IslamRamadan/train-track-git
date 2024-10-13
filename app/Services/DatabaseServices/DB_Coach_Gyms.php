<?php

namespace App\Services\DatabaseServices;

use App\Models\GymCoach;

class DB_Coach_Gyms
{


    public function create_gym_coach(mixed $gym_id, mixed $coach_id, mixed $privilege)
    {
        return GymCoach::create([
            'gym_id' => $gym_id,
            'coach_id' => $coach_id,
            'privilege' => $privilege,
        ]);
    }

    public function check_coach_assigned_to_gym(mixed $coach_gym_id, mixed $email)
    {
        return GymCoach::query()
            ->where('coach_id', $coach_gym_id)
            ->whereHas('coach', function ($query) use ($email) {
                $query->where('email', "=", $email);
            })
            ->exists();
    }

    public function get_gym_coaches(int $gym_id, int $admin_id, string|null $search, string|null $privilege)
    {
        $where = [
            'gym_id' => $gym_id,
        ];

        if (!empty($search)) {
            dd("it has a problem");
            $where['has:coach'] = [
                'orWhereHas' => [
                    'name' => 'LIKE', '%' . $search . '%',
                    'email' => 'LIKE', '%' . $search . '%',
                    'phone' => 'LIKE', '%' . $search . '%',
                ],
            ];
        }
        if (!empty($privilege)) {
            $where['privilege'] = $privilege ;
        }
        return GymCoach::query()
            ->where($where)
            ->where("coach_id", '!=', $admin_id)
            ->with(['coach'])
            ->get();

    }
}
