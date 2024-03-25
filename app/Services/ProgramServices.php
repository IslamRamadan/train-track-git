<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_ProgramExerciseVideos;
use App\Services\DatabaseServices\DB_Programs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProgramServices
{
    public function __construct(protected ValidationServices       $validationServices
        , protected DB_Programs                                    $DB_Programs, protected DB_Exercises $DB_Exercises,
                                protected DB_ProgramClients        $DB_ProgramClients,
                                protected DB_ProgramExerciseVideos $DB_ProgramExerciseVideos
    )
    {
    }

    public function index($request)
    {
        $this->validationServices->list_programs($request);
        $coach_id = $request->user()->id;
        $search = $request['search'];
        $programs = $this->DB_Programs->get_programs_with_coach($coach_id, $search);
        $programs_arr = $this->program_info_arr($programs);
        return sendResponse($programs_arr);
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
                "exercise_days" => $program->exercise_days,
                "clients_number" => $program->clients_number
            ];
            $programs_arr[] = $single_program;
        }
        return $programs_arr;
    }

    public function store($request)
    {
        $this->validationServices->add_program($request);
        $coach_id = $request->user()->id;
        $name = $request['name'];
        $description = $request['description'];
        $this->DB_Programs->add_program($coach_id, $name, $description);
        return sendResponse(['message' => "Program added successfully"]);
    }

    public function update($request)
    {
        $this->validationServices->edit_program($request);
        $name = $request['name'];
        $program_id = $request['program_id'];
        $description = $request['description'];
        $program = $this->DB_Programs->find_program($program_id);
        $this->DB_Programs->update_program($program, $name, $description);
        return sendResponse(['message' => "Program updated successfully"]);
    }

    /**
     * List program days to use in start day and end day
     * @param $request
     * @return JsonResponse
     */
    public function list_program_days($request): JsonResponse
    {
        $this->validationServices->list_program_days($request);
        $program_id = $request['program_id'];
        $get_programs_exercises = $this->DB_Exercises->get_program_exercises_days($program_id);
        return sendResponse(['days' => $get_programs_exercises]);
    }

    public function destroy($request)
    {
        $this->validationServices->delete_program($request);
        $program_id = $request['program_id'];
        $program = $this->DB_Programs->find_program($program_id);

//        Delete from program_clients table
        $this->DB_ProgramClients->delete_program_clients($program_id);
        DB::beginTransaction();
        if ($program->exercises()->exists()) {
            foreach ($program->exercises as $exercise) {
                //Delete from program_exercises_videos table
                $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
                //Delete from program_exercises table
                $this->DB_Exercises->delete_program_exercises($exercise);
            }
        }
//        Delete from programs table
        $this->DB_Programs->delete_program($program_id);
        DB::commit();

        return sendResponse(['message' => "Program deleted successfully"]);
//
    }

}
