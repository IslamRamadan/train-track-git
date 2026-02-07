<?php

namespace App\Services;

use App\Mail\GymInvitationMail;
use App\Mail\InvitationMail;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coach_Gyms;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_GymJoinRequest;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_GymLeaveRequest;
use App\Services\DatabaseServices\DB_GymPendingCoach;
use App\Services\DatabaseServices\DB_Gyms;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_OneToOneProgramStartingDate;
use App\Services\DatabaseServices\DB_ProgramClients;
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
                                protected NotificationServices $notificationServices, protected DB_GymLeaveRequest $DB_GymLeaveRequest,
                                protected ClientServices           $clientServices,
                                protected OneToOneExerciseServices $oneToOneExerciseServices,
                                protected DB_Clients               $DB_Clients,
                                protected OneToOneProgramServices  $oneToOneProgramServices,
                                protected DB_OneToOneProgram       $DB_OneToOneProgram,
                                protected DB_Exercises             $DB_Exercises,
                                protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected DB_OneToOneProgramStartingDate $DB_OneToOneProgramStartingDate,
                                protected DB_ProgramClients         $DB_ProgramClients,
                                protected ProgramServices          $programServices,
                                protected ExerciseServices         $exerciseServices,
                                protected DB_Programs              $DB_Programs
    )
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
            $gym_name = $request->user()->gym_coach->gym->name;

//            send notification to coach to notify him with the invitation
            try {
                Mail::to($email)->send(new GymInvitationMail($email, $gym_name, $admin_gym_id, $check_email_belongs_to_client->id));
                $title = "Gym Invitation";
                $message = "You are invited to join $gym_name gym";
                $this->notificationServices->send_notification_to_user($check_email_belongs_to_client->id, $title, $message, ["gym_name" => $gym_name]);
            } catch (\Exception $exception) {
                return sendError("Failed to send the email,Please try again later.");
            }

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
                "active_clients" => $coach->coach->active_clients,
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
                "coach_email" => $join_request->email,
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
        $gym_name = null;
        $coach_id = null;
        if ($this->check_coach_is_gym_admin($request)) {
            $is_admin = true;
            $gym_id = $request->user()->gym_coach->gym_id;
            $gym_name = $request->user()->gym_coach->gym->name;
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
            if ($status == "2") {
            $this->DB_Coach_Gyms->create_gym_coach($gym_id, $join_request->coach_id, "3");
                $title = "Join Request Accepted";
                $message = "Your request to join $gym_name gym is accepted";
                $this->notificationServices->send_notification_to_user($join_request->coach_id, $title, $message, ["gym_name" => $gym_name]);
            }
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
        $gym_name = $request->user()->gym_coach->gym->name;

        $leave_request = $this->DB_GymLeaveRequest->find_leave_request_with_id($request->leave_request_id, $gym_id);

        if ($leave_request) {
            $coach_id = $leave_request->coach_id;
            $gym_coach = $this->DB_Coach_Gyms->gym_coach($gym_id, $coach_id);
            if ($gym_coach) {
                DB::beginTransaction();
                if ($status == "2") {
                    $this->DB_Coach_Gyms->delete_gym_coach($gym_coach);
                    $title = "Leave Request Accepted";
                    $message = "Your request to leave $gym_name gym is accepted";
                    $this->notificationServices->send_notification_to_user($coach_id, $title, $message, ["gym_name" => $gym_name]);
                }
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

    /**
     * Edit coach privilege
     *
     * @param $request
     * @return JsonResponse
     */
    public function edit_coach_privilege($request)
    {
        $this->validationServices->edit_coach_privilege($request);
        $gym_id = $request->user()->gym_coach->gym_id;
        $privilege = $request->privilege;
        $coach_id = $request->coach_id;
        $gym_coach = $this->DB_Coach_Gyms->gym_coach(gym_id: $gym_id, coach_id: $coach_id);
        if ($gym_coach) {
            $this->DB_Coach_Gyms->update_coach_privilege($gym_coach, $privilege);
            return sendResponse(['message' => "Coach privilege updated successfully"]);
        }
        return sendError("This coach is not found in gym");
    }

    public function remove_coach_from_gym($request)
    {
        $this->validationServices->remove_coach_from_gym($request);
        $gym_id = $request->user()->gym_coach->gym_id;
        $coach_id = $request->coach_id;
        $gym_coach = $this->DB_Coach_Gyms->gym_coach(gym_id: $gym_id, coach_id: $coach_id);
        if ($gym_coach) {
            $this->DB_Coach_Gyms->delete_gym_coach($gym_coach);
            return sendResponse(['message' => "Coach removed from gym successfully"]);
        }
        return sendError("This coach is not found in gym");
    }

    public function send_join_request($request)
    {
        $this->validationServices->send_join_request($request);
        $coach_id = $request->user()->id;
        $coach_email = $request->user()->email;
        $gym_id = $request->gym_id;
        $check_coach_is_requested_to_gym = $this->DB_GymJoinRequest->check_coach_is_requested_to_gym(gym_id: null, coach_id: $coach_id);
        if ($check_coach_is_requested_to_gym) {
            return sendError("You already have a pending join request", 403);
        }
        $this->DB_GymJoinRequest->create_gym_join_request(gym_id: $gym_id, coach_id: $coach_id, admin_id: null, email: $coach_email);
        return sendResponse(['message' => "Request sent to gym successfully"]);
    }

    public function list($request)
    {
        $this->validationServices->list_gyms($request);
        $search = $request['search'];
        $gyms = $this->DB_Gyms->list_gyms($search);
        $gyms_arr = $this->gyms_arr($gyms);
        return sendResponse($gyms_arr);
    }

    public function gyms_arr($gyms): array
    {
        $gyms_arr = [];
        foreach ($gyms as $gym) {
            $gyms_arr[] = [
                "id" => strval($gym->id),
                "name" => $gym->name,
                "description" => $gym->description,
                "logo" => $gym->image_path,
            ];
        }
        return $gyms_arr;
    }

    public function edit($request)
    {
        $this->validationServices->edit_gym($request);

        $name = $request['name'];
        $description = $request['description'];
        $logo = $request['logo'];
        $gym = $request->user()->gym_coach->gym;
        DB::beginTransaction();
        if ($logo) {
            if ($gym->logo) $this->imageService->delete_image(image_title: $gym->logo, folder_name: 'gym_logos');
            $image_path = $this->imageService->save_image($logo, 'gym_logos');
            $gym->logo = $image_path;
            $gym->save();
        }

        $this->DB_Gyms->update_gym($gym, $name, $description);
        DB::commit();
        return sendResponse(['message' => "Gym updated successfully"]);
    }

    public function info($request)
    {
        $gym = $request->user()->gym_coach->gym;
        $gyms_arr = $this->gym_info($gym);
        return sendResponse($gyms_arr);
    }

    public function gym_info($gym)
    {
        return [
            "id" => strval($gym->id),
            "name" => $gym->name,
            "description" => $gym->description,
            "logo" => $gym->image_path,
        ];
    }

    public function list_coach_clients($request)
    {
        $coach_id = $request['coach_id'];
        $gym_id = $request->user()->gym_coach->gym_id;

        $check_coach_assigned_to_gym = $this->DB_Coach_Gyms->gym_coach(gym_id: $gym_id, coach_id: $coach_id);
        if (!$check_coach_assigned_to_gym) {
            return sendError("Coach is not assigned to your gym", 403);
        }
        return $this->clientServices->index($request);
    }

    public function list_client_program_exercises_by_date($request)
    {
        $oto_program_id = $request['client_program_id'];
        // Get the client ID from the request.
        $client_id = $this->DB_OneToOneProgram->find_oto_program($oto_program_id)->client_id;

        // Retrieve the admin privilege and gym ID from the logged-in user's data.
        $admin_gym_privilege = $request->user()->gym_coach->privilege;
        $admin_gym_id = $request->user()->gym_coach->gym_id;

        $validationResult = $this->validateClientCoach($client_id, $admin_gym_privilege, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }
        return $this->oneToOneExerciseServices->list_client_program_exercises_by_date($request);
    }

    /**
     * list client programs
     *
     * @param $request
     * @return JsonResponse|true
     */
    public function list_client_programs($request): JsonResponse|bool
    {
        // Get the client ID from the request.
        $client_id = $request['client_id'];

        // Retrieve the admin privilege and gym ID from the logged-in user's data.
        $admin_gym_privilege = $request->user()->gym_coach->privilege;
        $admin_gym_id = $request->user()->gym_coach->gym_id;

        $validationResult = $this->validateClientCoach($client_id, $admin_gym_privilege, $admin_gym_id);

        if ($validationResult !== true) {
            return $validationResult;
        }
        return $this->oneToOneProgramServices->index($request);
    }

    /**
     * Validate if the client coach belongs to the admins gym and
     * if the admin has the privilege to access the coach's clients.
     *
     * @param int|null $client_id
     * @param string $admin_gym_privilege
     * @param int $admin_gym_id
     * @return JsonResponse|true True if validation passes, or an error response if not.
     */
    public function validateClientCoach(int|null $client_id, string $admin_gym_privilege, int $admin_gym_id): bool|JsonResponse
    {
        if (!$client_id) {
            return sendError("Client not found", 404);
        }

        // Find the coach ID associated with the client.
        $coach_id = $this->DB_Clients->find_coach_id($client_id)->coach_id;

        // Check if the coach is assigned to the admin gym.
        $coach_gym = $this->DB_Coach_Gyms->gym_coach($admin_gym_id, $coach_id);

        // If the coach is not assigned to the admin gym, deny access.
        if (!$coach_gym) {
            return sendError("Client coach is not assigned to your gym", 403);
        }

        // If the coach is the gym owner and the admin is not, deny access.
//        if ($coach_gym->privilege == "1" && $admin_gym_privilege != "1") {
//            return sendError("Client coach is the gym owner, You can't see his clients", 403);
//        }

        return true;
    }

    /**
     * list programs exercises
     *
     * @param $request
     * @return bool|JsonResponse
     */
    public function list_programs_exercises($request)
    {
        $oto_program_id = $request['client_program_id'];
        if ($oto_program_id) {
            // Get the client id from the client_program_id
            $client_id = $this->DB_OneToOneProgram->find_oto_program($oto_program_id)->client_id;
        } else {
            // Get the client ID from the request.
            $client_id = $request['client_id'];
        }

        // Retrieve the admin privilege and gym ID from the logged-in user's data.
        $admin_gym_privilege = $request->user()->gym_coach->privilege;
        $admin_gym_id = $request->user()->gym_coach->gym_id;

        $validationResult = $this->validateClientCoach($client_id, $admin_gym_privilege, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->oneToOneExerciseServices->list_client_exercises($request);
    }

    /**
     * Validate access to a client program via gym membership
     *
     * @param int $program_id
     * @param int $admin_gym_id
     * @return JsonResponse|true True if validation passes, or an error response if not.
     */
    public function validateClientProgram(int $program_id, int $admin_gym_id): bool|JsonResponse
    {
        // Get the program
        $program = $this->DB_OneToOneProgram->find_oto_program($program_id);
        
        if (!$program) {
            return sendError("Program not found", 404);
        }

        // Get the client_id from the program
        $coach_id = $program->coach_id;

        // Check if the coach is assigned to the admin gym
        $coach_gym = $this->DB_Coach_Gyms->gym_coach_exists($admin_gym_id, $coach_id);

        // If the coach is not assigned to the admin gym, deny access
        if (!$coach_gym) {
            return sendError("Client coach is not assigned to your gym", 403);
        }

        return true;
    }

    /**
     * Validate access to a client exercise via gym membership
     *
     * @param int $exercise_id
     * @param int $admin_gym_id
     * @return JsonResponse|true True if validation passes, or an error response if not.
     */
    public function validateClientExercise(int $exercise_id, int $admin_gym_id): bool|JsonResponse
    {
        // Get the exercise
        $exercise = $this->DB_OneToOneProgramExercises->find_exercise($exercise_id);
        
        if (!$exercise) {
            return sendError("Exercise not found", 404);
        }

        // Get the program_id from the exercise, then get client_id
        $program = $exercise->one_to_one_program;
        
        if (!$program) {
            return sendError("Program not found", 404);
        }

        $coach_id = $program->coach_id;

        // Find the coach ID associated with the client
        // Check if the coach is assigned to the admin gym
        $coach_gym = $this->DB_Coach_Gyms->gym_coach_exists($admin_gym_id, $coach_id);

        // If the coach is not assigned to the admin gym, deny access
        if (!$coach_gym) {
            return sendError("Client coach is not assigned to your gym", 403);
        }

        return true;
    }


    /**
     * Add client exercise (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function add_client_exercise($request)
    {
        return $this->oneToOneExerciseServices->add_client_exercise($request);
    }

    /**
     * Update client exercise (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function update_client_exercise($request)
    {
        return $this->oneToOneExerciseServices->update_client_exercise($request);
    }

    /**
     * Delete client exercise (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_client_exercise($request)
    {
        return $this->oneToOneExerciseServices->delete_client_exercise($request);
    }

    /**
     * Copy client exercise (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function copy_client_exercise($request)
    {
        return $this->oneToOneExerciseServices->copy_client_exercise($request);
    }

    /**
     * Copy client exercise days (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function copy_client_exercise_days($request)
    {
        return $this->oneToOneExerciseServices->copy_client_exercise_days($request);
    }

    /**
     * Cut client exercise days (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function cut_client_exercise_days($request)
    {
        return $this->oneToOneExerciseServices->cut_client_exercise_days($request);
    }

    /**
     * Delete client exercise days (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_client_exercise_days($request)
    {
        return $this->oneToOneExerciseServices->delete_client_exercise_days($request);
    }

    /**
     * Delete client program (gym admin/owner only)
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_client_program($request)
    {
        return $this->oneToOneProgramServices->destroy($request);
    }

    /**
     * Validate access to a template program via gym membership
     *
     * @param int $program_id
     * @param int $admin_gym_id
     * @return JsonResponse|true True if validation passes, or an error response if not.
     */
    public function validateGymProgram(int $program_id, int $admin_gym_id): bool|JsonResponse
    {
        // Get only the coach_id (lightweight query - no relationships loaded)
        $coach_id = $this->DB_Programs->get_program_coach_id($program_id);
        
        if (!$coach_id) {
            return sendError("Program not found", 404);
        }

        // Verify coach is in same gym
        $coach_gym_exists = $this->DB_Coach_Gyms->gym_coach_exists($admin_gym_id, $coach_id);

        // If the coach is not in the same gym, deny access
        if (!$coach_gym_exists) {
            return sendError("Program coach is not assigned to your gym", 403);
        }

        return true;
    }

    /**
     * List programs for a coach in gym (similar to regular programs/list route)
     *
     * @param $request
     * @return JsonResponse
     */
    public function list_gym_programs($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        // Accept coach_id from request (optional - defaults to requesting admin/owner's ID)
        $coach_id = $request['coach_id'] ?? $request->user()->id;

        // Validate coach_id belongs to the same gym
        $coach_gym_exists = $this->DB_Coach_Gyms->gym_coach_exists($admin_gym_id, $coach_id);
        
        if (!$coach_gym_exists) {
            return sendError("Coach is not assigned to your gym", 403);
        }

        // Call ProgramServices::index() with the validated coach_id
        return $this->programServices->index($request, $coach_id);
    }

    /**
     * Update gym program
     *
     * @param $request
     * @return JsonResponse
     */
    public function update_gym_program($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->programServices->update($request);
    }

    /**
     * Delete gym program
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_gym_program($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->programServices->destroy($request);
    }

    /**
     * Update gym program sync
     *
     * @param $request
     * @return JsonResponse
     */
    public function update_gym_program_sync($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->programServices->update_sync($request);
    }

    /**
     * List gym program days
     *
     * @param $request
     * @return JsonResponse
     */
    public function list_gym_program_days($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->programServices->list_program_days($request);
    }

    /**
     * List gym program exercises
     *
     * @param $request
     * @return JsonResponse
     */
    public function list_gym_program_exercises($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->index($request);
    }

    /**
     * List gym program exercises by day
     *
     * @param $request
     * @return JsonResponse
     */
    public function list_gym_program_exercises_by_day($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->list_program_exercises_by_day($request);
    }

    /**
     * Add gym program exercise
     *
     * @param $request
     * @return JsonResponse
     */
    public function add_gym_program_exercise($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->create($request);
    }

    /**
     * Update gym program exercise
     *
     * @param $request
     * @return JsonResponse
     */
    public function update_gym_program_exercise($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['exercise_id'];

        // Get only the program_id (lightweight query - no relationships loaded)
        $program_id = $this->DB_Exercises->get_exercise_program_id($exercise_id);
        if (!$program_id) {
            return sendError("Exercise not found", 404);
        }

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->update($request);
    }

    /**
     * Delete gym program exercise
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_gym_program_exercise($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['exercise_id'];

        // Get only the program_id (lightweight query - no relationships loaded)
        $program_id = $this->DB_Exercises->get_exercise_program_id($exercise_id);
        if (!$program_id) {
            return sendError("Exercise not found", 404);
        }

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->destroy($request);
    }

    /**
     * Copy gym program exercise
     *
     * @param $request
     * @return JsonResponse
     */
    public function copy_gym_program_exercise($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['exercise_id'];
        $to_program_id = $request['to_program_id'];

        // Get only the program_id (lightweight query - no relationships loaded)
        $from_program_id = $this->DB_Exercises->get_exercise_program_id($exercise_id);
        if (!$from_program_id) {
            return sendError("Exercise not found", 404);
        }

        // Validate both source and destination programs
        $validationResult = $this->validateGymProgram($from_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->validateGymProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->copy($request);
    }

    /**
     * Copy gym program exercise days
     *
     * @param $request
     * @return JsonResponse
     */
    public function copy_gym_program_exercise_days($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];

        // Validate both programs
        $validationResult = $this->validateGymProgram($from_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->validateGymProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->copy_days($request);
    }

    /**
     * Cut gym program exercise days
     *
     * @param $request
     * @return JsonResponse
     */
    public function cut_gym_program_exercise_days($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];

        // Validate both programs
        $validationResult = $this->validateGymProgram($from_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->validateGymProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->cut_days($request);
    }

    /**
     * Delete gym program exercise days
     *
     * @param $request
     * @return JsonResponse
     */
    public function delete_gym_program_exercise_days($request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];

        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->exerciseServices->delete_days($request);
    }

    /**
     * Validate that a client belongs to a coach in the same gym
     *
     * @param int $client_id
     * @param int $admin_gym_id
     * @return JsonResponse|true True if validation passes, or an error response if not.
     */
    public function validateGymClient(int $client_id, int $admin_gym_id): bool|JsonResponse
    {
        // Find the coach ID associated with the client
        $coach_client = $this->DB_Clients->find_coach_id($client_id);
        
        if (!$coach_client) {
            return sendError("Client not found", 404);
        }

        $coach_id = $coach_client->coach_id;

        // Check if the coach is assigned to the admin gym
        $coach_gym = $this->DB_Coach_Gyms->gym_coach_exists($admin_gym_id, $coach_id);

        // If the coach is not assigned to the admin gym, deny access
        if (!$coach_gym) {
            return sendError("Client coach is not assigned to your gym", 403);
        }

        return true;
    }

    /**
     * List all clients from all coaches in the gym (aggregated)
     *
     * @param $request
     * @return JsonResponse
     */
    public function list_all_gym_clients($request)
    {
        $this->validationServices->list_clients($request);
        
        $gym_id = $request->user()->gym_coach->gym_id;
        $search = $request['search'] ?? null;
        $status = $request['status'] ?? 'all';

        // Get all coach IDs in the gym
        $coach_ids = $this->DB_Coach_Gyms->get_all_gym_coach_ids($gym_id);

        if (empty($coach_ids)) {
            return sendResponse([]);
        }

        // Get all clients from these coaches
        $clients = $this->DB_Clients->get_clients_by_coach_ids($coach_ids, $search, $status);

        // Format the results with coach information
        $clients_arr = [];
        foreach ($clients as $coach_client) {
            $client = $coach_client->client;
            $coach = $coach_client->coach;
            $client_info = $client->client ?? null;

            $status_text = match ($coach_client->status) {
                "0" => "Pending",
                "1" => "Active",
                "2" => "Archived",
                default => "Unknown",
            };

            $clients_arr[] = [
                "id" => $client->id,
                "name" => $client->name,
                "email" => $client->email,
                "phone" => $client->phone,
                "status" => $status_text,
                "coach_id" => $coach->id,
                "coach_name" => $coach->name,
                "payment_link" => $client_info->payment_link ?? "",
                "payment_amount" => $client_info->payment_amount ?? "",
                "renew_days" => $client_info->renew_days ?? "",
                "due_date" => $client->due_date ?? "",
                "weight" => $client_info->weight ?? "",
                "height" => $client_info->height ?? "",
                "fitness_goal" => $client_info->fitness_goal ?? "",
                "label" => $client_info->tag ?? "",
                "notes" => $client_info->notes ?? "",
            ];
        }

        return sendResponse($clients_arr);
    }

    /**
     * Assign a template program (from any gym coach) to a client (from any gym coach)
     *
     * @param $request
     * @return JsonResponse
     */
    public function assign_gym_program_to_client($request)
    {
        $this->validationServices->assign_program_to_client($request);
        
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['program_id'];
        $clients_id = $request['clients_id'];
    
        // Validate that the program belongs to a coach in the same gym
        $validationResult = $this->validateGymProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }
    
        // Validate that all clients belong to coaches in the same gym
        foreach ($clients_id as $client_id) {
            $validationResult = $this->validateGymClient($client_id, $admin_gym_id);
            if ($validationResult !== true) {
                return $validationResult;
            }
        }
    
        // Get the program to find its coach_id
        $parent_program = $this->DB_Programs->find_program($program_id);
        
        if (!$parent_program) {
            return sendError("Program not found", 404);
        }
    
        // Use the program's original coach_id instead of the requesting admin's id
        $program_coach_id = $parent_program->coach_id;
    
        // Delegate to ClientServices with the program's coach_id
        return $this->clientServices->assign_program_to_client($request, $program_coach_id);
    }
}
