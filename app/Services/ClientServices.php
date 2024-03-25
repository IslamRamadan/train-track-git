<?php

namespace App\Services;

use App\Mail\InvitationMail;
use App\Services\DatabaseServices\DB_Clients;
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
use Illuminate\Support\Facades\Mail;

class ClientServices
{
    public function __construct(protected ValidationServices               $validationServices,
                                protected DB_Clients                       $DB_Clients, protected DB_Exercises $DB_Exercises,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected DB_Programs                      $DB_Programs,
                                protected DB_OneToOneProgram               $DB_OneToOneProgram,
                                protected DB_Users                         $DB_Users,
                                protected DB_ProgramClients                $DB_ProgramClients,
                                protected DB_PendingClients                $DB_PendingClients,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
    )
    {
    }


    public function index($request)
    {
        $this->validationServices->list_clients($request);
        $coach_id = $request->user()->id;
        $search = $request['search'];
        $status = $request['status'];
        $clients = $this->DB_Clients->get_all_clients($coach_id, $search, $status);
        $pending_clients = $this->DB_PendingClients->get_coach_pending_clients($coach_id, $search);
        $clients_arr = $this->client_info_arr($clients, $pending_clients, $status);
        return sendResponse($clients_arr);
    }

    /**
     * @param $clients
     * @return array that has id,name,email,phone,status
     */
    public function client_info_arr($clients, $pending_clients, $status): array
    {
        $clients_arr = [];
        foreach ($clients as $client) {
            $single_client = [
                "id" => $client->client->id,
                "name" => $client->client->name,
                "email" => $client->client->email,
                "phone" => $client->client->phone,
                "status" => $client->status//0 for pending , 1 for active,2 for archived
            ];
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
                        "status" => "0"//0 for pending , 1 for active,2 for archived
                    ];
                    $clients_arr[] = $single_client;
                }
            }
        }

        return $clients_arr;
    }

    /**
     * Assign program to client
     * @param $request
     * @return JsonResponse
     */
    public function assign_program_to_client($request): JsonResponse
    {
        $this->validationServices->assign_program_to_client($request);
        $coach_id = $request->user()->id;
        $clients_id = $request['clients_id'];
        $program_id = $request['program_id'];
        $start_date = $request['start_date'];
        $start_day = $request['start_day'];
        $end_day = $request['end_day'];
        $notify_client = $request['notify_client'];

//       1-get all program exercises days
        $program_exercises = $this->DB_Exercises->get_program_exercises_day_sorted($program_id, $start_day, $end_day);
        if (count($program_exercises) > 0) {
//       2-assign end day to the last day if not exist

            $end_day = $end_day != "" ? $end_day : $program_exercises->last()->day;
//       3-get the difference between two days as number
            $start_and_difference = intval($end_day) - intval($start_day);

//       4-increase this difference to the start date to get the end date
            $end_date = $this->get_date_after_n_days($start_date, $start_and_difference);
            $failed_clients = [];
            $success_clients = [];
            foreach ($clients_id as $client_id) {
                $client_info = $this->DB_Users->get_user_info($client_id);
                //5-check if the user has any conflict in client schedule (will check in one_to_one_program_exercises table with client_id
                //and start_date and end_date)
                $check_client_has_exercises_between_two_dates = $this->DB_OneToOneProgramExercises->
                check_client_has_exercises_between_two_dates($client_id, $start_date, $end_date);
                //6-if there is any conflict then fail
                if (count($check_client_has_exercises_between_two_dates) > 0) {
                    $failed_clients[] = $client_info->name;
                    continue;
                }
                $success_clients[] = $client_info->name;

                //if there is no conflict then create the program with exercises
                //8-get the parent program
                $parent_program = $this->DB_Programs->find_program($program_id);

                //9-create row with client_id and program_id in program_clients table
                $this->DB_ProgramClients->create_program_client($program_id, $client_id);
                //10-create the custom program assigned to user
                $one_to_program = $this->DB_OneToOneProgram->create_one_to_program($parent_program, $client_id, $coach_id);
                //11-create the custom program exercises assigned to custom program
                foreach ($program_exercises as $exercise) {
                    $exercise_date = $this->get_date_after_n_days($start_date, $exercise->day - $start_day);//get the day after the current day
                    $oto_exercise = $this->DB_OneToOneProgramExercises->create_one_to_one_program_exercises($exercise, $exercise_date, $one_to_program->id);
                    //add exercises videos if exists
                    $this->add_exercises_videos($oto_exercise->id, $exercise);
                }
            }

        } else {
//            return error the program must have at least one exercise
            return sendError("the program must have at least one exercise", 401);
        }
        if (count($success_clients) > 0 && count($failed_clients) == 0) {
            $success_clients_string = implode(",", $success_clients);
            return sendResponse(['message' => "Program assigned successfully to client(s) " . $success_clients_string]);
        } elseif (count($failed_clients) > 0 && count($success_clients) == 0) {
            $failed_clients_string = implode(",", $failed_clients);
            return sendError("Failed to assign program to client(s) " . $failed_clients_string . " because they have exercises in this time");
        } else {
            $success_clients_string = implode(",", $success_clients);
            $failed_clients_string = implode(",", $failed_clients);

            return sendResponse(['message' => "Program assigned successfully to client(s) " . $success_clients_string . " and failed to assign program to client(s) "
                . $failed_clients_string . " because they have exercises in this time"]);
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
        $coach_email = $request->user()->email;
        $email = $request['email'];
        Mail::to($email)->send(new InvitationMail($email, $coach_email));

        $this->DB_PendingClients->create_pending_client($coach_id, $email);

        return sendResponse(['message' => "Client Invited Successfully"]);
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

        $this->DB_Users->update_user($client_id, $name
            , $email
            , $phone);

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
        $client_id = $request['client_id'];
        $status = $request['status'];
        $this->DB_Clients->archive_client(client_id: $client_id, status: $status);
        $type = $status == "2" ? "archived" : "unarchived";
        return sendResponse(['message' => "Client " . $type . " successfully"]);
    }
}
