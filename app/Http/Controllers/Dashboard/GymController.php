<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\CoachService;
use App\Services\Dashboard\GymService;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function __construct(protected GymService $coachService)
    {
    }

    public function accept_gym_invitation(Request $request)
    {
        return $this->coachService->accept_gym_invitation($request);
    }
}
