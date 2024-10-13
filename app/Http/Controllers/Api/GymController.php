<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GymServices;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function __construct(protected GymServices $gymServices)
    {
    }

    /**
     * Display a listing of gyms.
     */
    public function index(Request $request)
    {
        return $this->gymServices->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->gymServices->store($request);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        return $this->gymServices->update($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        return $this->gymServices->destroy($request);
    }

    /**
     * Invite coach to gym
     */
    public function invite_coach_to_gym(Request $request)
    {
        return $this->gymServices->invite_coach_to_gym($request);
    }

    /**
     * list gym coaches
     */
    public function list_gym_coaches(Request $request)
    {
        return $this->gymServices->list_gym_coaches($request);
    }
}
