<?php

namespace App\Services\Dashboard;

use App\Services\DatabaseServices\DB_Coach_Gyms;
use App\Services\DatabaseServices\DB_GymJoinRequest;

class GymService
{

    public function __construct(protected DB_GymJoinRequest $DB_GymJoinRequest, protected DB_Coach_Gyms $DB_Coach_Gyms)
    {
    }

    public function accept_gym_invitation($request)
    {
        $coach_id = $request->coach_id;
        $gym_id = $request->gym_id;
        $join_request = $this->DB_GymJoinRequest->find_join_request(gym_id: $gym_id, coach_id: $coach_id);
        if ($join_request) {
            $this->DB_GymJoinRequest->update_join_request(join_request: $join_request, status: "2");
            $this->DB_Coach_Gyms->create_gym_coach($gym_id, $coach_id, "3");
            $gym_name=$join_request->gym->name;
            return view('gym.gym-invitation-accepted',compact('gym_name'));

        } else {
            return sendError("Join request is not found");
        }
    }
}
