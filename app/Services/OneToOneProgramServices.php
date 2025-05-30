<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_ExerciseLogVideos;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_OtoExerciseComments;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_Users;
use Illuminate\Support\Facades\DB;

class OneToOneProgramServices
{
    public function __construct(protected ValidationServices               $validationServices
        , protected DB_OneToOneProgram                                     $DB_OneToOneProgram,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected DB_ExerciseLog                   $DB_ExerciseLog,
                                protected DB_OtoExerciseComments           $DB_OtoExerciseComments,
                                protected DB_ProgramClients           $DB_ProgramClients,
                                protected DB_Users             $DB_Users,
                                protected DB_ExerciseLogVideos $DB_ExerciseLogVideos,
    )
    {
    }

    /**
     * @param $programs
     * @return array that has id  , name , description
     */
    public function program_info_arr($programs): array
    {
        $programs_arr = [];
        foreach ($programs as $program) {
            $single_program = [
                "id" => $program->id,
                "name" => $program->name,
                "description" => $program->description,
                "exercises_number" => $program->exercises_number
            ];
            $programs_arr[] = $single_program;
        }
        return $programs_arr;
    }

    public function index($request, $coach_id = null)
    {
        $this->validationServices->list_client_ono_programs($request);
        $search = $request['search'];
        $client_id = $request['client_id'];
        $client_info = $this->DB_Users->get_user_info($client_id);
        $programs = $this->DB_OneToOneProgram->get_client_oto_programs($coach_id, $client_id, $search);
        $programs_arr = $this->program_info_arr($programs);
        $result = [
            "client_id" => $client_info->id,
            "client_name" => $client_info->name,
            "client_phone" => $client_info->phone,
            "client_email" => $client_info->email,
            "programs" => $programs_arr,
        ];
        return sendResponse($result);
    }

    public function destroy($request)
    {
        $this->validationServices->delete_client_ono_programs($request);

        $program_id = $request['client_program_id'];

        $program = $this->DB_OneToOneProgram->find_oto_program($program_id);

        DB::beginTransaction();
        $this->DB_ProgramClients->delete_program_clients_with_oto_id($program_id);
        if ($program->exercises()->exists()) {
            foreach ($program->exercises as $exercise) {
                //Delete from program exercises videos table
                $this->DB_OneToOneProgramExerciseVideos->delete_exercise_videos($exercise);
                //Delete from program exercises log table
                if ($exercise->log()->exists()) {
                    $this->DB_ExerciseLogVideos->delete_exercise_log_videos($exercise->log);
                    $this->DB_ExerciseLog->delete_exercise_log($exercise);
                }
                //Delete from program exercises table
                $this->DB_OneToOneProgramExercises->delete_program_exercises($exercise);
            }
        }
        if ($program->comments()->exists()) {
            $program->comments()->delete();
        }
//        Delete from programs table
        $this->DB_OneToOneProgram->delete_program($program_id);
        DB::commit();

        return sendResponse(['message' => "Program deleted successfully"]);
//

    }


}
