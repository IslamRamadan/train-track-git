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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->gymServices->store($request);
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

    /**
     * Edit coach privilege
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit_coach_privilege(Request $request)
    {
        return $this->gymServices->edit_coach_privilege($request);
    }

    /**
     * Remove coach from gym
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_coach_from_gym(Request $request)
    {
        return $this->gymServices->remove_coach_from_gym($request);
    }

    /**
     * Send join request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send_join_request(Request $request)
    {
        return $this->gymServices->send_join_request($request);
    }

    /**
     * List Gyms
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return $this->gymServices->list($request);
    }

    /**
     * Edit Gym info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request)
    {
        return $this->gymServices->edit($request);
    }

    /**
     * Get Gym info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request)
    {
        return $this->gymServices->info($request);
    }

    /**
     * List coach clients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_coach_clients(Request $request)
    {
        return $this->gymServices->list_coach_clients($request);
    }

    /**
     * list program exercises by day
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_client_program_exercises_by_date(Request $request)
    {
        return $this->gymServices->list_client_program_exercises_by_date($request);
    }

    /**
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function list_client_programs(Request $request)
    {
        return $this->gymServices->list_client_programs($request);
    }


    public function list_programs_exercises(Request $request)
    {
        return $this->gymServices->list_programs_exercises($request);
    }

}
