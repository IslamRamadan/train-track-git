<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Services\CoachServices;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_PendingClients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    public function __construct(protected CoachServices $coachServices, protected DB_Packages $DB_Packages,
                                protected DB_Clients    $DB_Clients, protected DB_PendingClients $DB_PendingClients,
                                protected DB_Coaches    $DB_Coaches
    )
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

    public function check_package_limit(Request $request): JsonResponse
    {
        return $this->coachServices->check_package_limit($request);
    }

    public function get_package(Request $request): JsonResponse
    {
        $coaches = Coach::all();
        foreach ($coaches as $coach) {
            $coach_id = $coach->user_id;
            $active_clients = $this->DB_Clients->get_active_clients($coach_id);
            $pending_clients = $this->DB_PendingClients->get_pending_clients($coach_id);
            Log::info($coach_id . " has " . $active_clients . "active clients and " . $pending_clients . " pending");
            $total_coach_clients = $active_clients + $pending_clients;
            $package = $this->DB_Packages->get_appropriate_package($total_coach_clients, ">=");
            $this->DB_Coaches->update_coach_package(coach_id: $coach_id, package_id: $package->id);
        }
        return sendResponse(["msg" => "Done"]);
    }

    public function list_packages(Request $request): JsonResponse
    {
        return $this->coachServices->list_packages($request);
    }
  public function list_payments(Request $request): JsonResponse
    {
        return $this->coachServices->list_payments($request);
    }

}
