<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Services\DatabaseServices\DB_Coach_Gyms;
use App\Services\DatabaseServices\DB_GymJoinRequest;
use App\Services\DatabaseServices\DB_GymLeaveRequest;
use App\Services\DatabaseServices\DB_GymPendingCoach;
use App\Services\DatabaseServices\DB_Gyms;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class GymServices
{
    public function __construct(protected ValidationServices   $validationServices
        , protected DB_Gyms                                    $DB_Gyms, protected DB_Coach_Gyms $DB_Coach_Gyms,
                                protected ImageService         $imageService, protected DB_GymPendingCoach $DB_GymPendingCoach,
                                protected DB_Users             $DB_Users, protected DB_GymJoinRequest $DB_GymJoinRequest,
                                protected NotificationServices $notificationServices, protected DB_GymLeaveRequest $DB_GymLeaveRequest
    )
    {
    }

    public function index($request)
    {

    }

    /**
     * add a new gym logic
     * @param $request
     * @return JsonResponse
     */
    public function store($request)
    {
        // Validate the request data

        $this->validationServices->add_gym($request);
        $owner_id = $request->user()->id;
        $name = $request->name;
        $description = $request->description;
        $logo = $request->logo;

        // Check if the coach is already a gym admin
        $check_coach_is_gym_admin = $this->check_coach_is_gym_admin($request);
        if ($check_coach_is_gym_admin) {
            return sendError("Coach is already gym admin", 403);
        }

        // Save the gym logo if provided
        $logo_name = null;
        if ($logo) {
            try {
                $logo_name = $this->imageService->save_image($logo, 'gym_logos');
            } catch (\Exception $exception) {
                return sendError("Failed to upload the gym logo");
            }
        }
        DB::beginTransaction();
        // Create the gym
        $gym = $this->DB_Gyms->create_gym($owner_id, $name, $description, $logo_name);
        // Create Gym Coach
        $this->DB_Coach_Gyms->create_gym_coach($gym->id, $owner_id, "1");
        DB::commit();
        return sendResponse(['message' => "Gym added successfully"]);
    }

    public function update($request)
    {

    }

    public function destroy($request)
    {

    }

    /**
     * Invite coach to gym
     *
     * @param $request
     * @return JsonResponse
     *
     */
    public function invite_coach_to_gym($request)
    {
        $check_email_belongs_to_client = $this->DB_Users->find_user_by_email(email: $request->email);

        $this->validationServices->invite_coach_to_gym($request, $check_email_belongs_to_client);


        $coach_id = $request->user()->id;
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $email = $request->email;
        $admin_email = $request->user()->email;
        $admin_name = $request->user()->name;
//        check email is invited to gym
        if ($this->DB_GymJoinRequest->check_email_is_invited_to_gym($email, $admin_gym_id)) return sendError("Coach is already invited to your gym", 403);;

//      check if the invited coach is assigned to another gym
        if ($this->DB_Coach_Gyms->check_coach_assigned_to_gym($admin_gym_id, $email)) return sendError("Coach is already assigned to gym", 403);

//        if user exists in system
        if ($check_email_belongs_to_client) {
            // check if this email coach is not invited before with status pending to this gym
            if ($this->DB_GymJoinRequest->check_coach_is_requested_to_gym($admin_gym_id, $check_email_belongs_to_client->id)) return sendError("Coach is already invited to your gym", 403);;

//            send notification to coach to notify him with the invitation
            $title = "Gym Invitation";
            $message = "$admin_name invited you to join his gym";
            $this->notificationServices->send_notification_to_user($coach_id, $title, $message);


        } else {
            // else then will send email to coach
            try {
                Mail::to($email)->send(new InvitationMail($email, $admin_email));
            } catch (\Exception $exception) {
                return sendError("Failed to send the email,Please try again later.");
            }
        }
        // add the join request
        $this->DB_GymJoinRequest->create_gym_join_request($admin_gym_id, $check_email_belongs_to_client?->id, $coach_id, $email);
        return sendResponse(['message' => "Coach invited successfully"]);
    }

    /**
     * check coach is gym admin
     * @param $request
     * @return bool
     */
    private function check_coach_is_gym_admin($request)
    {
        return $request->user()->isGymAdmin == 1;
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public function list_gym_coaches($request)
    {
        $this->validationServices->list_gym_coaches($request);

        $gym_id = $request->user()->gym_coach->gym_id;
        $admin_id = $request->user()->id;
        $search = $request['search'];
        $privilege = $request['privilege'];
//      get gym coaches except the logged in coach
        $gym_coaches = $this->DB_Coach_Gyms->get_gym_coaches($gym_id, $admin_id, $search, $privilege);
//        $pending_gym_coaches = $this->DB_GymPendingCoach->get_gym_pending_coaches($coach_id, $search);
        $coaches_arr = $this->gym_coaches_arr($gym_coaches);
        return sendResponse($coaches_arr);
    }

    public function gym_coaches_arr($gym_coaches): array
    {
        $coaches_arr = [];

        foreach ($gym_coaches as $coach) {
            $privilege = match ($coach->privilege) {
                "1" => "Owner",
                "2" => "Admin",
                default => "Coach",
            };

//            $status = match ($coach->client->status) {
//                1 => "Active",
//                2 => "Archived",
//                default => "Pending",
//            };

            $coaches_arr[] = [
                "id" => $coach->coach->id,
                "name" => $coach->coach->name,
                "email" => $coach->coach->email,
                "phone" => $coach->coach->phone,
                "due_date" => $coach->coach->due_date??"",
                "privilege" => $privilege,
            ];
        }

//        if ($status === "all" || $status === "pending") {
//            foreach ($pending_gym_coaches as $coach) {
//                $coaches_arr[] = [
//                    "id" => "",
//                    "name" => "",
//                    "email" => $coach->email,
//                    "phone" => "",
//                    "due_date" => "",
//                    "privilege" => "0", // 0 for pending, 1 for active, 2 for archived
//                    "status" => "Pending",
//                ];
//            }
//        }

        return $coaches_arr;
    }

    public function list_join_requests($request)
    {
        $search = $request['search'];
//        if coach is admin then he will get all his gym join requests (Received and Sent)
        if ($this->check_coach_is_gym_admin($request)) {
            $is_admin = true;
            $admin_gym_id = $request->user()->gym_coach->gym_id;
            $gym_join_requests = $this->DB_GymJoinRequest->get_gym_join_requests($admin_gym_id, $search);
        } else {
//        if coach is not an admin then he will get all gyms join requests (Received and Sent)
            $is_admin = false;
            $coach_id = $request->user()->id;
            $gym_join_requests = $this->DB_GymJoinRequest->get_coach_gym_join_requests($coach_id, $search);
        }
        $coaches_arr = $this->join_requests_arr($gym_join_requests, $is_admin);
        return sendResponse($coaches_arr);
    }

    public function join_requests_arr($gym_join_requests, $is_admin): array
    {
        $coaches_arr = [];

        foreach ($gym_join_requests as $join_request) {
            $send_type = $this->getRequestType($is_admin, $join_request->admin_id);
            $request_status = match ($join_request->status) {
                "0" => "Rejected",
                "1" => "Pending",
                default => "Accepted",
            };

            $coaches_arr[] = [
                "id" => strval($join_request->id),
                "gym_name" => $join_request->gym->name,
                "gym_admin_email" => $join_request->admin ? $join_request->admin->email : "",
                "coach_email" => $join_request->coach->email,
                "request_date" => Carbon::parse($join_request->created_at)->toDateString(),
                "request_time" => Carbon::parse($join_request->created_at)->toTimeString(),
                "request_type" => $send_type,
                "request_status" => $request_status,
            ];
        }

        return $coaches_arr;
    }

    /**
     * change join request status logic
     *
     * @param $request
     * @return JsonResponse
     */
    public function change_join_request_status($request)
    {
        $this->validationServices->change_join_request_status($request);
        $status = $request->status;
        $gym_id = null;
        $coach_id = null;
        if ($this->check_coach_is_gym_admin($request)) {
            $is_admin = true;
            $gym_id = $request->user()->gym_coach->gym_id;
        } else {
            $is_admin = false;
            $coach_id = $request->user()->id;
        }

        $join_request = $this->DB_GymJoinRequest->find_join_request($request->join_request_id, $gym_id, $coach_id);

        if ($join_request) {
            $send_type = $this->getRequestType($is_admin, $join_request->admin_id);
            if ($send_type == "Sent") {
                return sendError("You can't change a request that you sent");
            }
            $this->DB_GymJoinRequest->update_join_request($join_request, null, $status);
        } else {
            return sendError("Join request is not found");
        }
        return sendResponse(['message' => "Join request status updated successfully"]);

    }

    /**
     * @param bool $is_admin
     * @param $admin_id
     * @return string
     */
    private function getRequestType(bool $is_admin, $admin_id): string
    {
        return $is_admin ? ($admin_id == null ? 'Received' : 'Sent') : ($admin_id == null ? 'Sent' : 'Received');
    }

    public function send_leave_request($request)
    {
        $gym_id = $request->user()->gym_coach->gym_id;
        $coach_id = $request->user()->id;
        $find_leave_request = $this->DB_GymLeaveRequest->find_leave_request($gym_id, $coach_id);
        if ($find_leave_request) {
            return sendError("There is already a pending leave request for this gym", 403);
        }
        $this->DB_GymLeaveRequest->create_leave_request($gym_id, $coach_id);
        return sendResponse(['message' => "Leave request added successfully"]);
    }

    public function list_leave_requests($request)
    {
        $this->validationServices->list_leave_requests($request);
        $gym_id = $request->user()->gym_coach->gym_id;
        $search = $request->search;
        $status = $request->status;

        $gym_leave_requests = $this->DB_GymLeaveRequest->list_leave_requests($gym_id, $search, $status);
        $leave_requests_arr = $this->list_leave_requests_arr($gym_leave_requests);
        return sendResponse($leave_requests_arr);
    }

    public function list_leave_requests_arr($gym_leave_requests): array
    {
        $leave_requests_arr = [];

        foreach ($gym_leave_requests as $leave_request) {
            $request_status = match ($leave_request->status) {
                "0" => "Rejected",
                "1" => "Pending",
                default => "Accepted",
            };

            $leave_requests_arr[] = [
                "id" => strval($leave_request->id),
                "gym_name" => $leave_request->gym->name,
                "coach_email" => $leave_request->coach->email,
                "request_date" => Carbon::parse($leave_request->created_at)->toDateString(),
                "request_time" => Carbon::parse($leave_request->created_at)->toTimeString(),
                "request_status" => $request_status,
            ];
        }

        return $leave_requests_arr;
    }

    public function change_leave_request_status($request)
    {
        $this->validationServices->change_leave_request_status($request);
        $status = $request->status;
        $gym_id = $request->user()->gym_coach->gym_id;

        $leave_request = $this->DB_GymLeaveRequest->find_leave_request_with_id($request->leave_request_id, $gym_id);

        if ($leave_request) {
            $coach_id = $leave_request->coach_id;
            $gym_coach = $this->DB_Coach_Gyms->gym_coach($gym_id, $coach_id);
            if ($gym_coach) {
                DB::beginTransaction();
                if ($status == "2") $this->DB_Coach_Gyms->delete_gym_coach($gym_coach);
                $this->DB_GymLeaveRequest->update_leave_request($leave_request, $status);
                DB::commit();
            } else {
                return sendError("Coach is not found in gym");
            }

        } else {
            return sendError("Leave request is not found");
        }
        return sendResponse(['message' => "Leave request status updated successfully"]);

    }

}
