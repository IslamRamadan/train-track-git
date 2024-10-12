<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Services\DatabaseServices\DB_Coach_Gyms;
use App\Services\DatabaseServices\DB_GymJoinRequest;
use App\Services\DatabaseServices\DB_GymPendingCoach;
use App\Services\DatabaseServices\DB_Gyms;
use App\Services\DatabaseServices\DB_Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class GymServices
{
    public function __construct(protected ValidationServices   $validationServices
        , protected DB_Gyms                                    $DB_Gyms, protected DB_Coach_Gyms $DB_Coach_Gyms,
                                protected ImageService         $imageService, protected DB_GymPendingCoach $DB_GymPendingCoach,
                                protected DB_Users             $DB_Users, protected DB_GymJoinRequest $DB_GymJoinRequest,
                                protected NotificationServices $notificationServices
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

        // Check if the sender coach is already a gym admin
        $check_coach_is_gym_admin = $this->check_coach_is_gym_admin($request);
        if (!$check_coach_is_gym_admin) {
            return sendError("Sender coach is not a gym admin", 403);
        }
        $coach_id = $request->user()->id;
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $email = $request->email;
        $admin_email = $request->user()->email;
        $admin_name = $request->user()->name;
//        check email is invited to gym
        if ($this->DB_GymPendingCoach->check_email_is_invited_to_gym($admin_gym_id, $email)) return sendError("Coach is already invited to your gym", 403);;

//      check if the invited coach is assigned to another gym
        if ($this->DB_Coach_Gyms->check_coach_assigned_to_gym($admin_gym_id, $email)) return sendError("Coach is already assigned to gym", 403);

//        if user exists in system
        if ($check_email_belongs_to_client) {
            // check if this email coach is not invited before with status pending to this gym
            if ($this->DB_GymJoinRequest->check_coach_is_requested_to_gym($admin_gym_id, $check_email_belongs_to_client->id)) return sendError("Coach is already invited to your gym", 403);;

//            send notification to coach to notify him with the invitation
            $title = "Gym Invitation";
            $message="$admin_name "
            $this->notificationServices->send_notification_to_user($coach_id, $title, $message);


            // add the request directly with the coach id
            $this->DB_GymJoinRequest->create_gym_join_request($admin_gym_id, $check_email_belongs_to_client->id, $coach_id);
        } else {
            // else then will add email to pending gym coaches
            try {
                Mail::to($email)->send(new InvitationMail($email, $admin_email));
            } catch (\Exception $exception) {
                return sendError("Failed to send the email,Please try again later.");
            }
            $this->DB_GymPendingCoach->add_email_to_pending_gym_requests($coach_id, $admin_gym_id, $email);
        }
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
}
