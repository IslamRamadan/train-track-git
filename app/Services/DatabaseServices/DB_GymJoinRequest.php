<?php

namespace App\Services\DatabaseServices;

use App\Models\GymJoinRequest;

class DB_GymJoinRequest
{


    public function create_gym_join_request(mixed $gym_id, mixed $coach_id, mixed $admin_id = null, mixed $status = "1")
    {
        return GymJoinRequest::query()
            ->create([
                'gym_id' => $gym_id,
                'coach_id' => $coach_id,
                'admin_id' => $admin_id,
                'status' => $status,
            ]);
    }

    public function check_coach_is_requested_to_gym(mixed $gym_id, mixed $coach_id, mixed $status = "1")
    {
        return GymJoinRequest::query()
            ->where([
                'gym_id' => $gym_id,
                'coach_id' => $coach_id,
                'status' => $status,
            ])
            ->exists();
    }

}
