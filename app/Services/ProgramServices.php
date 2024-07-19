<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_ProgramExerciseVideos;
use App\Services\DatabaseServices\DB_Programs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProgramServices
{
    public function __construct(protected ValidationServices               $validationServices
        , protected DB_Programs                                            $DB_Programs, protected DB_Exercises $DB_Exercises,
                                protected DB_ProgramClients                $DB_ProgramClients,
                                protected DB_ProgramExerciseVideos         $DB_ProgramExerciseVideos,
                                protected DB_OneToOneProgram               $DB_OneToOneProgram,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected DB_ExerciseLog                   $DB_ExerciseLog,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected ImageService                     $imageService,
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
                "type" => $program->type_text,
                "starting_date" => $program->type == "1" ? $program->starting_date : "",
                "sync" => $program->type == "1" ? $program->sync : "",
                "exercise_days" => $program->exercise_days,
                "clients_number" => $program->clients_number,
                "image" => $program->image_path
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
        $type = $request['type'];
        $sync = $request['sync'] ?? "0";
        $starting_date = $request['starting_date'];
        $image = $request['image'];

        if ($image) {
            try {
                $image_path = $this->imageService->save_image($image, 'programs');
            } catch (\Exception $exception) {
                return sendError("Failed to upload the image");
            }
        }

        $this->DB_Programs->add_program($coach_id, $name, $description, $type, $starting_date, $sync, $image_path);
        return sendResponse(['message' => "Program added successfully"]);
    }

    public function update($request)
    {
        $this->validationServices->edit_program($request);
        $name = $request['name'];
        $program_id = $request['program_id'];
        $description = $request['description'];
        $type = $request['type'];
        $starting_date = $request['starting_date'];
        $image = $request['image'];
        $program = $this->DB_Programs->find_program($program_id);
        DB::beginTransaction();
        if ($image) {
            if ($program->image) $this->imageService->delete_image(image_title: $program->image, folder_name: 'programs');
            $image_path = $this->imageService->save_image($image, 'programs');
            $program->image = $image_path;
            $program->save();
        }
        if ($program->sync == "1") {
            $this->sync_on_update_program($program_id, $name, $description);
        }
        $this->DB_Programs->update_program($program, $name, $description, $type, $starting_date);
        DB::commit();
        return sendResponse(['message' => "Program updated successfully"]);
    }

    public function update_sync($request)
    {
        $this->validationServices->edit_program_sync($request);
        $sync = $request['sync'];
        $program_id = $request['program_id'];
        $program = $this->DB_Programs->find_program($program_id);
        $this->DB_Programs->update_program_sync($program, $sync);
        return sendResponse(['message' => "Program sync updated successfully"]);
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
        DB::beginTransaction();
        $this->DB_ProgramClients->delete_program_clients($program_id);
        if ($program->one_to_one_program) {
            foreach ($program->one_to_one_program as $program_client) {
                if ($program_client->oto_program_id) {
                    if ($program->sync == "0") {
                        $this->delete_oto_programs($program_client->oto_program);
                    } else {
                        if ($program->exercises()->exists()) {
                            foreach ($program->exercises as $exercise) {
                                $this->DB_OneToOneProgramExercises->remove_realation_btween_oto_and_template_exercise($exercise->id);
                            }
                        }
                    }
                }
            }
        }


        if ($program->exercises()->exists()) {
            foreach ($program->exercises as $exercise) {
                //Delete from program_exercises_videos table
                $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
                //Delete from program_exercises table
                $this->DB_Exercises->delete_program_exercises($exercise);
            }
        }
//        Delete from programs table
        if ($program->image) $this->imageService->delete_image(image_title: $program->image, folder_name: 'programs');
        $this->DB_Programs->delete_program($program_id);
        DB::commit();

        return sendResponse(['message' => "Program deleted successfully"]);
//
    }

    /**
     * @param mixed $program_id
     * @param mixed $name
     * @param mixed $description
     * @return void
     */
    public function sync_on_update_program(mixed $program_id, mixed $name, mixed $description): void
    {
        $related_oto_programs = $this->DB_ProgramClients->get_program_related_oto_programs($program_id);
        if (count($related_oto_programs) > 0) {
            foreach ($related_oto_programs as $related_program) {
                $this->DB_OneToOneProgram->update_oto_program($related_program->oto_program, $name, $description);
            }
        }
    }

    private function delete_oto_programs($program)
    {
        $this->DB_ProgramClients->delete_program_clients_with_oto_id($program->id);
        if ($program->exercises()->exists()) {
            foreach ($program->exercises as $exercise) {
                //Delete from program exercises videos table
                $this->DB_OneToOneProgramExerciseVideos->delete_exercise_videos($exercise);
                //Delete from program exercises log table
                $this->DB_ExerciseLog->delete_exercise_log($exercise);
                //Delete from program exercises table
                $this->DB_OneToOneProgramExercises->delete_program_exercises($exercise);
            }
        }
        if ($program->comments()->exists()) {
            $program->comments()->delete();
        }
//        Delete from programs table
        $this->DB_OneToOneProgram->delete_program($program->id);
    }

}
