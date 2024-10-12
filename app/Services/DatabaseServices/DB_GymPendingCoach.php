<?php

namespace App\Services\DatabaseServices;

use App\Models\GymPendingCoach;

class DB_GymPendingCoach
{


    public function check_email_is_invited_to_gym(mixed $gym_id, mixed $email)
    {
        return GymPendingCoach::query()
            ->where([
                'gym_id' => $gym_id,
                'email' => $email,
            ])
            ->exists();
    }

    public function add_email_to_pending_gym_requests(mixed $coach_id, mixed $coach_gym_id, mixed $email)
    {
        return GymPendingCoach::query()
            ->create([
                'gym_id' => $coach_gym_id,
                'admin_id' => $coach_id,
                'email' => $email,
            ]);
    }
}
