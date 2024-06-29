<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CoachServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    public function __construct(protected CoachServices $coachServices)
    {

    }


    public function coach_dashboard(Request $request)
    {
        return $this->coachServices->coach_dashboard($request);
    }

    public function clients_activities(Request $request)
    {
        return $this->coachServices->get_clients_activities($request);
    }

    /**
     *  client update info
     * @param Request $request
     * @return JsonResponse
     */
    public function update_info(Request $request): JsonResponse
    {
        return $this->coachServices->update_info($request);
    }

    /**
     *  client update info
     * @param Request $request
     * @return JsonResponse
     */
    public function list_client_logs(Request $request): JsonResponse
    {
        return $this->coachServices->list_client_logs($request);
    }

    public function update_due_date(Request $request): JsonResponse
    {
        return $this->coachServices->update_due_date($request);
    }

    public function create_payment_link(Request $request): JsonResponse
    {
        return $this->coachServices->create_payment_link($request);
    }


}
