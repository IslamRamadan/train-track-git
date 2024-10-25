<?php

namespace App\Services\DatabaseServices;


use App\Models\GymLeaveRequest;

class DB_GymLeaveRequest
{
    public function create_leave_request(mixed $gym_id, mixed $coach_id, mixed $status = "1")
    {
        return GymLeaveRequest::query()->create([
            'gym_id' => $gym_id,
            'coach_id' => $coach_id,
            'status' => $status,
        ]);
    }

    public function update_leave_request($leave_request, $status)
    {
        $leave_request->update([
            'status' => $status,
        ]);
    }

    public function find_leave_request(mixed $gym_id, mixed $coach_id, mixed $status = "1")
    {
        return GymLeaveRequest::query()->where([
            'gym_id' => $gym_id,
            'coach_id' => $coach_id,
            'status' => $status,
        ])->first();
    }

    public function find_leave_request_with_id(mixed $leave_request_id, mixed $gym_id, mixed $status = "1")
    {
        return GymLeaveRequest::query()->where([
            'id' => $leave_request_id,
            'gym_id' => $gym_id,
            'status' => $status,
        ])->first();
    }

    public function list_leave_requests(mixed $gym_id, mixed $search, mixed $status)
    {
        return GymLeaveRequest::query()
            ->where('gym_id', $gym_id)
            ->search($search)
            ->when($status != null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->get();
    }
}
