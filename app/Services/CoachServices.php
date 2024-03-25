<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class CoachServices
{
    public function __construct(protected ValidationServices          $validationServices,
                                protected DB_Clients                  $DB_Clients,
                                protected DB_Coaches                  $DB_Coaches,
                                protected DB_Users                    $DB_Users,
                                protected DB_ExerciseLog              $DB_ExerciseLog,
                                protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises
    )
    {
    }


    /**
     * Get coach some statistics like : number of clients,
     * number of workouts done
     *
     * @param $request
     * @return JsonResponse
     */
    public function coach_dashboard($request)
    {
        $coach_id = $request->user()->id;
//        number of clients
        $number_of_clients = $this->DB_Clients->get_coach_clients_count($coach_id);

//        number of clients active
        $number_of_active_clients = $this->DB_Clients->get_coach_clients_count($coach_id, "1");

        $coach_info = $this->DB_Users->get_user_info($coach_id);

//        Workouts done today
        $today = Carbon::now()->toDateString();
        $get_workouts_done_today = $this->DB_OneToOneProgramExercises->get_workouts_done_today($today, $coach_id);
        $clients_activity = [];
        $done_workout = 0;
        if (count($get_workouts_done_today) > 0) {
            foreach ($get_workouts_done_today as $client_today_exercises) {
                $client_info = $this->DB_Users->get_user_info($client_today_exercises[0]->one_to_one_program->client_id);
                $done_exercises = 0;
                foreach ($client_today_exercises as $exercise) {
                    if ($exercise->is_done == "1") {
                        $done_exercises++;
                    }
                }
                if (count($client_today_exercises) == $done_exercises) {
                    $done_workout++;
                }
                $clients_activity[] = [
                    'client_id' => $client_info->id,
                    'client_name' => $client_info->name,
                    'today_exercises' => count($client_today_exercises),
                    'done_exercises' => $done_exercises,
                ];
            }
        }

//        get the today's logs of the coach clients
        $clients_logs_today = $this->DB_ExerciseLog->list_coach_clients_logs_today($coach_id, $today);
        $list_logs_arr = $this->list_logs_arr($clients_logs_today);
//        Daily Activity done exercises/total workouts
        return sendResponse([
            'coach_id' => $coach_info->id,
            'coach_name' => $coach_info->name,
            'coach_phone' => $coach_info->phone,
            'coach_email' => $coach_info->email,
            'coach_gym' => $coach_info->coach->gym,
            'coach_speciality' => $coach_info->coach->speciality,
            'coach_certificates' => $coach_info->coach->certificates,
            'total_clients' => $number_of_clients,
            'active_clients' => $number_of_active_clients,
            'done_workouts' => $done_workout,
            'clients_activity' => $clients_activity,
            'today_logs' => $list_logs_arr
        ]);
    }

    public function update_info($request)
    {
        $this->validationServices->coach_update_info($request);

        $coach_id = $request->user()->id;
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $gym = $request->gym;
        $speciality = $request->speciality;
        $certificates = $request->certificates;

        $this->DB_Users->update_user($coach_id, $name
            , $email
            , $phone);
        $this->DB_Coaches->update_coach($coach_id, $gym, $speciality, $certificates);

        return sendResponse(['message' => "Coach information updated successfully"]);

    }

    public function list_client_logs($request)
    {
        $this->validationServices->list_client_logs($request);
        $client_id = $request->client_id;

        $logs = $this->DB_ExerciseLog->list_cient_logs($client_id);
        return sendResponse($this->list_logs_arr($logs));

    }

    public function list_logs_arr(Collection|array $logs)
    {
        $logs_arr = [];
        if ($logs) {
            foreach ($logs as $log) {
                $single_log_arr = [];
                $single_log_arr['client_id'] = $log->exercise->one_to_one_program->client_id;
                $single_log_arr['client_name'] = $log->exercise->one_to_one_program->client->name;
                $single_log_arr['program_id'] = $log->exercise->one_to_one_program->id;
                $single_log_arr['program_name'] = $log->exercise->one_to_one_program->name;
                $single_log_arr['exercise_id'] = $log->exercise->id;
                $single_log_arr['exercise_name'] = $log->exercise->name;
                $single_log_arr['exercise_description'] = $log->exercise->description;
                $single_log_arr['log_id'] = $log->id;
                $single_log_arr['log_sets'] = $log->sets;
                $single_log_arr['log_details'] = $log->details;
                $single_log_arr['log_date'] = $log->created_at->format("Y-m-d");
                $single_log_arr['log_time'] = $log->created_at->format("H:i:s");
                $logs_arr[] = $single_log_arr;
            }
        }
        return $logs_arr;
    }

}
