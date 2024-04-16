<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Services\ClientServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    public function __construct(protected ClientServices $clientServices)
    {

    }


    public function index(Request $request)
    {
        return $this->clientServices->index($request);
    }

    /**
     * Assign program to client
     * @param Request $request
     * @return JsonResponse
     */
    public function assign_program_to_client(Request $request): JsonResponse
    {
        return $this->clientServices->assign_program_to_client($request);

    }

    /**
     * Invite client to join coach family
     * @param Request $request
     * @return JsonResponse
     */
    public function assign_client_to_coach(Request $request): JsonResponse
    {
        return $this->clientServices->assign_client_to_coach($request);
    }

    /**
     * Remove  client invitation
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_client_invitation(Request $request): JsonResponse
    {
        return $this->clientServices->remove_client_invitation($request);
    }

    /**
     * get client profile info
     * @param Request $request
     * @return JsonResponse
     */
    public function profile_info(Request $request): JsonResponse
    {
        return $this->clientServices->profile_info($request);
    }

    /**
     *  client update info
     * @param Request $request
     * @return JsonResponse
     */
    public function update_info(Request $request): JsonResponse
    {
        return $this->clientServices->update_info($request);
    }

    /**
     *  client dashboard some stats
     * @param Request $request
     * @return JsonResponse
     */
    public function client_dashboard(Request $request): JsonResponse
    {
        return $this->clientServices->client_dashboard($request);
    }

    /**
     *  client archive account
     * @param Request $request
     * @return JsonResponse
     */
    public function archive_account(Request $request): JsonResponse
    {
        return $this->clientServices->archive_account($request);
    }

    /**
     *  client delete account
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_client(Request $request): JsonResponse
    {
        return $this->clientServices->delete_client($request);
    }
    public function delete(): JsonResponse
    {
        return $this->clientServices->delete();
    }

    /**
     *  coach archive client
     * @param Request $request
     * @return JsonResponse
     */
    public function coach_archive_client(Request $request): JsonResponse
    {
        return $this->clientServices->coach_archive_client($request);
    }
}
