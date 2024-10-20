<?php

namespace App\Services\DatabaseServices;

use App\Models\GymJoinRequest;

class DB_GymJoinRequest
{


    public function create_gym_join_request(mixed $gym_id, mixed $coach_id, mixed $admin_id, mixed $email, mixed $status = "1")
    {
        return GymJoinRequest::query()
            ->create([
                'gym_id' => $gym_id,
                'coach_id' => $coach_id,
                'admin_id' => $admin_id,
                'status' => $status,
                'email' => $email,
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

    public function get_gym_join_requests(mixed $gym_id, $search)
    {
        return GymJoinRequest::query()
            ->with(['coach', 'gym', 'admin'])
            ->where('gym_id', $gym_id)->search($search)->orderBy('created_at', 'DESC')->get();
    }

    public function get_coach_gym_join_requests(mixed $coach_id, $search)
    {
        return GymJoinRequest::query()
            ->with(['coach', 'gym', 'admin'])
            ->where('coach_id', $coach_id)->search($search)->orderBy('created_at', 'DESC')->get();

    }

    public function check_email_is_invited_to_gym(mixed $email, mixed $gym_id, mixed $status = "1")
    {
        return GymJoinRequest::query()
            ->where([
                'gym_id' => $gym_id,
                'email' => $email,
                'status' => $status,
            ])
            ->exists();
    }

    public function find_email_is_invited_to_gym(mixed $email)
    {
        return GymJoinRequest::query()
            ->where([
                'email' => $email,
                'status' => "1",
            ])
            ->first();
    }

    public function update_join_request($join_request, mixed $coach_id = null, mixed $status = null)
    {
        $updateData = [];

        if (!is_null($coach_id)) {
            $updateData['coach_id'] = $coach_id;
        }

        if (!is_null($status)) {
            $updateData['status'] = $status;
        }

        if (!empty($updateData)) {
            $join_request->update($updateData);
        }
    }

    public function find_join_request($id, mixed $gym_id = null, mixed $coach_id = null, mixed $status = "1")
    {
        $query = GymJoinRequest::query()
            ->where(['id' => $id, 'status' => $status]);
        if ($gym_id != null) {
            $query->where('gym_id', $gym_id);
        }
        if ($coach_id != null) {
            $query->where('coach_id', $coach_id);
        }
        return $query->first();

    }

}
