<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientServices
{
    public function __construct(protected ValidationServices               $validationServices,
                                protected DB_Clients                       $DB_Clients,
                                protected DB_Coaches                       $DB_Coaches,
                                protected DB_Exercises                     $DB_Exercises,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected DB_Programs                      $DB_Programs,
                                protected DB_OneToOneProgram               $DB_OneToOneProgram,
                                protected DB_Users                         $DB_Users,
                                protected DB_ProgramClients                $DB_ProgramClients,
                                protected DB_PendingClients                $DB_PendingClients,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected CoachServices                    $coachServices,
    )
    {
    }


    public function index($request)
    {
        $this->validationServices->list_clients($request);
        $coach_id = $request['coach_id'] ?: $request->user()->id;
        $search = $request['search'];
        $status = $request['status'];
        $clients = $this->DB_Clients->get_all_clients($coach_id, $search, $status);
        $pending_clients = $this->DB_PendingClients->get_coach_pending_clients($coach_id, $search);
        $clients_arr = $this->client_info_arr($clients, $pending_clients, $status);
        return sendResponse($clients_arr);
    }

    public function client_details($request)
    {
        $this->validationServices->client_details($request);
        $coach_id = $request['coach_id'] ?: $request->user()->id;
        $client_id = $request['client_id'];

        $clientDetails = $this->DB_Clients->list_client_details($coach_id, $client_id);
        $single_client = $clientDetails ? $this->clientInfoArr($clientDetails) : [];
        return sendResponse($single_client);
    }

    /**
     * Get the clients that made(Comment and log and update status) in last 7 days logic
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_active_clients(Request $request)
    {
        $this->validationServices->list_active_clients($request);
        $coach_id = $request->user()->id;
        $search = $request['search'];
        $date_from = Carbon::now()->subWeek();
        $date_to = Carbon::now();
        $clients = $this->DB_Clients->get_active_clients_between_dates($coach_id, $search, $date_from, $date_to);
        $clients_arr = $this->active_clients_info_arr($clients);
        return sendResponse($clients_arr);
    }
    /**
     * @param $clients
     * @param $pending_clients
     * @param $status
     * @return array that has id,name,email,phone,status
     */
    public function client_info_arr($clients, $pending_clients, $status): array
    {
        $clients_arr = [];
        foreach ($clients as $client) {
            $single_client = $this->clientInfoArr($client);
            $clients_arr[] = $single_client;
        }
        if ($status == "all" || $status == "pending") {
            if (count($pending_clients) > 0) {
                foreach ($pending_clients as $client) {
                    $single_client = [
                        "id" => "",
                        "name" => "",
                        "email" => $client->email,
                        "phone" => "",
                        "payment_link" => "",
                        "due_date" => "",
                        "status" => "0",//0 for pending , 1 for active,2 for archived
                        "weight" => "",
                        "height" => "",
                        "fitness_goal" => "",
                        "label" => "",
                        "notes" => "",
                    ];
                    $clients_arr[] = $single_client;
                }
            }
        }

        return $clients_arr;
    }

    /**
     * @param $clients
     * @return array that has id,name,email,phone,status
     */
    public function active_clients_info_arr($clients): array
    {
        $clients_arr = [];
        foreach ($clients as $client) {
            $single_client = [
                "id" => $client->client->id,
                "name" => $client->client->name,
                "last_active" => $client->client->last_active
            ];
            $clients_arr[] = $single_client;
        }


        return $clients_arr;
    }

    /**
     * Assign a program to a client
     * @param $request
     * @return JsonResponse
     */
    public function assign_program_to_client($request): JsonResponse
    {
        $this->validationServices->assign_program_to_client($request);
        $coach_id = $request->user()->id;
        $clients_id = $request['clients_id'];
        $program_id = $request['program_id'];
        $start_date = $request['start_date'];//
        $start_day = $request['start_day'];//
        $end_day = $request['end_day'];
        $notify_client = $request['notify_client'];
        $find_program_type = $this->DB_Programs->find_program(program_id: $program_id);
        if ($find_program_type->type == "0") {
            $clientAssignedBefore = $this->DB_ProgramClients->programAssignedToClientBefore($program_id, $clients_id);
            if ($clientAssignedBefore) {
                return sendError("This template program already assigned to this client");
            }
            if (($start_date == null || $start_day == null)) {
            return sendError("Start date and Start day is required when program type is normal");
        }
        }

        if ($find_program_type->type == "1") {
            $start_date = $find_program_type->starting_date;//
            if (count($this->DB_Exercises->get_program_exercises_days($program_id)) > 0) {
                if ($start_day == null) {
                    $start_day = 1;
                }
            } else {
                return sendError("the program must have at least one exercise", 401);
            }
        }

//       1-get all program exercises days
        $program_exercises = $this->DB_Exercises->get_program_exercises_day_sorted($program_id, $start_day, $end_day);
        if (count($program_exercises) > 0) {
//       2-assign end day to the last day if not exist

            $end_day = $end_day != "" ? $end_day : $program_exercises->last()->day;
//       3-get the difference between two days as number
            $start_and_difference = intval($end_day) - intval($start_day);

//       4-increase this difference to the start date to get the end date
            $end_date = $this->get_date_after_n_days($start_date, $start_and_difference);
            $success_clients = [];
            foreach ($clients_id as $client_id) {
                $client_info = $this->DB_Users->get_user_info($client_id);
                //5-check if the user has any conflict in client schedule (will check in one_to_one_program_exercises table with client_id
                //and start_date and end_date)
                $success_clients[] = $client_info->name;

                //if there is no conflict then create the program with exercises
                //8-get the parent program
                $parent_program = $this->DB_Programs->find_program($program_id);

                //9-create the custom program assigned to user
                $one_to_program = $this->DB_OneToOneProgram->create_one_to_program($parent_program->name, $parent_program->description, $client_id, $coach_id);
                //10-create row with client_id and program_id in program_clients table
                $this->DB_ProgramClients->create_program_client($program_id, $client_id, $one_to_program->id);
                //11-create the custom program exercises assigned to custom program
                foreach ($program_exercises as $exercise) {
                    $exercise_date = $this->get_date_after_n_days($start_date, $exercise->day - $start_day);//get the day after the current day
                    $oto_exercise = $this->DB_OneToOneProgramExercises->create_one_to_one_program_exercises($exercise->name,
                        $exercise->description, $exercise->extra_description, $exercise->arrangement, $exercise_date,
                        $one_to_program->id, $exercise->id);
                    //add exercises videos if exists
                    $this->add_exercises_videos($oto_exercise->id, $exercise);
                }
            }

        } else {
//            return error the program must have at least one exercise
            return sendError("the program must have at least one exercise", 401);
        }
        if (count($success_clients) > 0) {
            $success_clients_string = implode(",", $success_clients);
            return sendResponse(['message' => "Program assigned successfully to client(s) " . $success_clients_string]);
        } else {
            return sendError("Error,please try again.", 401);
        }
    }

    /**
     * get date after n days from start date
     * @param mixed $start_date
     * @param int $n_days
     * @return string
     */
    private function get_date_after_n_days(mixed $start_date, int $n_days): string
    {
        // Parse the start date into a Carbon instance
        $carbonDate = Carbon::parse($start_date);

        // Add 5 days to the original date
        $end_date = $carbonDate->addDays($n_days);

        // Format the end date as needed (e.g., Y-m-d)
        return $end_date->format('Y-m-d');
    }

    /**
     * Invite client to join coach family
     * @param $request
     * @return JsonResponse
     */
    public function assign_client_to_coach($request): JsonResponse
    {
        $this->validationServices->assign_client_to_coach($request);

        $coach_id = $request->user()->id;
        $coach_email = $request->user()->name;
        $email = $request['email'];

        list($coach_package, $upgrade) = $this->coachServices->get_coach_package($coach_id);
        if ($upgrade) return sendError("Need to upgrade to package " . $coach_package->name . " that has " . $coach_package->clients_limit . " clients limit with " . $coach_package->amount . " EGP monthly.");
        try {
            Mail::to($email)->send(new InvitationMail($email, $coach_email));
        } catch (\Exception $exception) {
            return sendError("Failed to send the email,Please try again later.");
        }

        $this->DB_PendingClients->create_pending_client($coach_id, $email);


        return sendResponse(['message' => "Client Invited Successfully"]);
    }

    /**
     * Invite client to join coach family
     * @param $request
     * @return JsonResponse
     */
    public function remove_client_invitation($request): JsonResponse
    {
        $this->validationServices->remove_client_invitation($request);

        $coach_id = $request->user()->id;
        $email = $request['email'];

        $this->DB_PendingClients->delete_pending_client(email: $email);

        return sendResponse(['message' => "Client Invitation removed Successfully"]);
    }

    public function profile_info($request)
    {
        $client_id = $request->user()->id;

        $client_info = $this->DB_Users->get_user_info($client_id);

        $client_info_arr = [
            'name' => $client_info->name,
            'email' => $client_info->email,
            'phone' => $client_info->phone,
        ];

        return sendResponse($client_info_arr);

    }

    public function update_info($request)
    {
        $this->validationServices->update_info($request);

        $client_id = $request->user()->id;
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $weight = $request->weight;
        $height = $request->height;
        $fitness_goal = $request->fitness_goal;
        $label = $request->label;
        $notes = $request->notes;
        $country_id = $request->country_id;
        $gender_id = $request->gender_id;

        $this->DB_Users->update_user($client_id, $name, $email, $phone, $country_id, $gender_id);
        $client=$this->DB_Clients->get_client_info($client_id);
        if ($client){
            $this->DB_Clients->update_client_info($client,[
                'weight' => $weight,
                'height' => $height,
                'fitness_goal' => $fitness_goal,
                'label' => $label,
                'notes' => $notes,
            ]);
        }
        else{
            $this->DB_Clients->create_client_data([
                'user_id'=>$client_id,
                'weight' => $weight,
                'height' => $height,
                'fitness_goal' => $fitness_goal,
                'label' => $label,
                'notes' => $notes,
            ]);
        }


        return sendResponse(['message' => "Client information updated successfully"]);

    }

    public function client_dashboard($request)
    {
        $client_id = $request->user()->id;
        $todayYMD = Carbon::now()->format('Y-m-d');
        $total_today_exercises = $this->DB_OneToOneProgramExercises->total_today_exercises($client_id, $todayYMD);
        $total_today_done_exercises = $this->DB_OneToOneProgramExercises->total_today_done_exercises($client_id, $todayYMD);
        $done_exercises_percentage = $total_today_exercises > 0 ? $total_today_done_exercises / $total_today_exercises * 100 : 0;
        $done_exercises_percentage = round($done_exercises_percentage, 1);

        $client_programs = $this->DB_OneToOneProgram->get_all_client_oto_programs($client_id);

        $programs = [];
        if (count($client_programs) > 0) {
            foreach ($client_programs as $program) {
                $single_program_arr = [];
                $single_program_arr['program_id'] = $program->id;
                $single_program_arr['program_name'] = $program->name;
                $single_program_arr['program_image'] = $program->program_client ? $program->program_client->program->image_path : "";
                $single_program_arr['program_exercises'] = $this->DB_OneToOneProgramExercises->get_all_program_exercises_count($program->id);
                $single_program_arr['program_done_exercises'] = $this->DB_OneToOneProgramExercises->get_all_program_done_exercises_count($program->id);
                $programs[] = $single_program_arr;
            }
        }
        $result['today_done_exercises_percentage'] = $done_exercises_percentage;
        $result['programs_progress'] = $programs;
        return sendResponse($result);

    }

    public function archive_account($request)
    {
        $client_id = $request->user()->id;
        $this->DB_Clients->archive_client($client_id, "2");
        return sendResponse(['message' => "Account archived successfully"]);
    }

    public function delete_client($request)
    {
        $this->validationServices->delete_client($request);
        $client_id = $request->client_id;
        //delete comments
        $client_info = $this->DB_Users->get_user_for_delete($client_id);

        DB::beginTransaction();
        //delete comments
        if ($client_info->program_clients()->exists()) {
            $client_info->program_clients()->delete();
        }
        if ($client_info->client_programs()->exists()) {
            foreach ($client_info->client_programs as $program) {
                if ($program->exercises()->exists()) {
                    foreach ($program->exercises as $exercise) {
                        if ($exercise->log()->exists()) {
                            //delete exercises logs
                            $exercise->log->log_videos()->delete();
                            $exercise->log->delete();
                        }
                        if ($exercise->videos()->exists()) {
                            //delete exercises videos
                            $exercise->videos()->delete();
                        }
                        //delete exercises
                        $exercise->delete();
                    }
                }
                //delete comments
                if ($program->comments()->exists()) {
                    $program->comments()->delete();
                }


                //delete programs
                $program->delete();
            }
        }
        if ($client_info->notifications()->exists()) {
            $client_info->notifications()->delete();
        }
        if ($client_info->payments()->exists()) {
            $client_info->payments()->delete();
        }
        if ($client_info->notification_token()->exists()) {
            $client_info->notification_token()->delete();
        }
        //delete coach client
        $client_info->coach_client_client()->delete();
        //delete from clients table
        if ($client_info->client()->exists()) {
        $client_info->client->delete();
        }
        //delete user
        $client_info->delete();
        DB::commit();
        return sendResponse(['message' => "Account deleted successfully"]);
    }

    private function add_exercises_videos($oto_exercise_id, $exercise)
    {
        if ($exercise->videos()->exists()) {
            foreach ($exercise->videos as $video) {
                $this->DB_OneToOneProgramExerciseVideos->create_program_exercise_video($oto_exercise_id, $video);
            }
        }
    }

    public function coach_archive_client($request)
    {
        $this->validationServices->archive_client($request);
        $coach_id = $request->user()->id;
        $client_id = $request['client_id'];
        $status = $request['status'];
        if ($status == "1") {
            //  check the active clients if
            list($coach_package, $upgrade) = $this->coachServices->get_coach_package($coach_id);
            if ($upgrade) return sendError("Need to upgrade to package " . $coach_package->name . " that has " . $coach_package->clients_limit . " clients limit with " . $coach_package->amount . " EGP monthly.");
        }
        $this->DB_Clients->archive_client(client_id: $client_id, status: $status);
        $type = $status == "2" ? "archived" : "unarchived";

        return sendResponse(['message' => "Client " . $type . " successfully"]);
    }

    public function delete()
    {
        return sendResponse(['message' => "Client deleted successfully"]);
    }

    public function getClientsHaveNotExercisesInDate(Request $request)
    {
        $this->validationServices->getClientsHaveNotExercisesInDate($request);
        $coach_id = $request->user()->id;
        $date = $request->date;
        $clientHasExercisesInDate = $this->DB_OneToOneProgram->getClientHasExercisesInDate($coach_id, $date);
        $clients = $this->DB_Users->get_clients_have_not_exercises_in_date($coach_id, $date, $clientHasExercisesInDate);

        $clients_arr = [];
        foreach ($clients as $client) {
            $single_client = [
                "id" => $client->id,
                "name" => $client->name,
            ];
            $clients_arr[] = $single_client;
        }
        return sendResponse($clients_arr);

    }

    /**
     * Get Clients Assigned To Template Program Logic
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientsAssignedToProgram(Request $request): JsonResponse
    {
        $this->validationServices->getClientsAssignedToProgram($request);
        $coach_id = $request->user()->id;
        $program_id = $request->program_id; // template program id
        $clients = $this->DB_ProgramClients->get_clients_assigned_to_program($coach_id, $program_id);
        $clients_arr = [];
        foreach ($clients as $client) {
            $single_client = [
                "id" => $client->client_id,
                "name" => $client->client->name,
            ];
            $clients_arr[] = $single_client;
        }
        return sendResponse($clients_arr);
    }

    /**
     * @param mixed $client
     * @return array
     */
    private function clientInfoArr(mixed $client): array
    {
        return [
            "id" => $client->client->id,
            "name" => $client->client->name,
            "email" => $client->client->email,
            "phone" => $client->client->phone,
            "country_id" => $client->client->country_id ?? "",
            "country_name" => $client->client?->country?->name ?? "",
            "gender_id" => $client->client->gender_id ?? "",
            "gender_name" => $client->client?->gender?->name ?? "",
            "payment_link" => $client->client->client->payment_link ?? "",
            "tag" => $client->client->client->tag ?? "",
            "due_date" => $client->client->due_date ?? "",
            "status" => $client->status,//0 for pending , 1 for active,2 for archived
            "weight" => $client?->client?->client?->weight ?? "",
            "height" => $client?->client?->client?->height ?? "",
            "fitness_goal" => $client?->client?->client?->fitness_goal ?? "",
            "label" => $client?->client?->client?->label ?? "",
            "notes" => $client?->client?->client?->notes ?? "",
        ];
    }


}
