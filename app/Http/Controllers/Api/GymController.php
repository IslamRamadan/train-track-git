<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GymServices;
use Illuminate\Http\JsonResponse;
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_coaches(Request $request)
    {
        return $this->gymServices->list_gym_coaches($request);
    }

    /**
     * list gym join requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_join_requests(Request $request)
    {
        return $this->gymServices->list_join_requests($request);
    }

    /**
     * list gym Admins
     *
     * @param Request $request
     * @return mixed
     */
    public function list_gym_admins(Request $request)
    {
        return $this->gymServices->list_gym_admins($request);
    }

    /**
     * change join request status
     *
     * @param Request $request
     * @return mixed
     */
    public function change_join_request_status(Request $request)
    {
        return $this->gymServices->change_join_request_status($request);
    }

    /**
     * send leave request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send_leave_request(Request $request)
    {
        return $this->gymServices->send_leave_request($request);
    }

    /**
     * list leave requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_leave_requests(Request $request)
    {
        return $this->gymServices->list_leave_requests($request);
    }
    /**
     * change leave request status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function change_leave_request_status(Request $request)
    {
        return $this->gymServices->change_leave_request_status($request);
    }

}
