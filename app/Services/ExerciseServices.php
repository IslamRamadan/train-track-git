<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_ProgramExerciseVideos;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseServices
{
    public function __construct(protected ValidationServices       $validationServices,
                                protected DB_Exercises             $DB_Exercises,
                                protected DB_ProgramExerciseVideos $DB_ProgramExerciseVideos
    )
    {
    }

    private function rearrange_program_exercises($exercises, $order): void
    {
        $current_order = 1;
        foreach ($exercises as $exercise) {
            if ($current_order == $order) {
                $current_order++;
            }
            $this->DB_Exercises->update_exercise($exercise, $exercise->name, $exercise->description, $exercise->extra_description, $current_order);
            $current_order++;
        }
    }

    public function index($request)
    {
        $this->validationServices->list_program_exercises($request);
        $program_id = $request['program_id'];
        $week = $request['week'];
        $days_arr = $this->get_week_arr($week);
        $program_exercises = $this->DB_Exercises->get_program_exercises($program_id, $days_arr);
        $program_exercises_arr = $this->list_program_exercises_arr($program_exercises);
        return sendResponse($program_exercises_arr);
    }

    public function list_program_exercises_by_day($request)
    {
        $this->validationServices->list_program_exercises_by_day($request);
        $program_id = $request['program_id'];
        $day = $request['day'];
        $program_exercises = $this->DB_Exercises->get_program_exercises_by_day($program_id, $day);
        $program_exercises_arr = $this->list_program_exercises_by_day_arr($program_exercises);
        return sendResponse($program_exercises_arr);
    }

    public function create($request)
    {
        $this->validationServices->add_program_exercise($request);
        $program_id = $request['program_id'];
        $name = $request['name'];
        $day = $request['day'];
        $description = $request['description'];
        $extra_description = $request['extra_description'];
        $videos = $request['videos'];
        $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($program_id, $day);
        DB::beginTransaction();
        $exercise = $this->DB_Exercises->add_exercise($name, $description, $extra_description, $day, $exercise_arrangement, $program_id);
        $this->add_exercises_videos($exercise->id, $videos);
        DB::commit();

        return sendResponse(['exercise_id' => $exercise->id, 'message' => "Exercise added successfully"]);
    }

    public function copy($request)
    {
        $this->validationServices->copy_program_exercise($request);
        $exercise_id = $request['exercise_id'];
        $to_program_id = $request['to_program_id'];
        $day = $request['day'];
        $exercise = $this->DB_Exercises->find_exercise($exercise_id);
        $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $day);
        DB::beginTransaction();
        $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description, $exercise->extra_description, $day, $exercise_arrangement, $to_program_id);
        if ($exercise->videos()->exists()) {
            $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
        }
        DB::commit();

        return sendResponse(['exercise_id' => $copied_exercise->id, 'message' => "Exercise copied successfully"]);
    }

    function copy_days($request)
    {
        $this->validationServices->copy_program_exercise_days($request);
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];
        $copied_days = $request['copied_days'];
        $day = $request['start_day'];
        foreach ($copied_days as $copied_day) {
            $day_exercises = $this->DB_Exercises->get_program_exercises_by_day(program_id: $from_program_id, day: $copied_day);
            if ($day_exercises) {
                DB::beginTransaction();
                foreach ($day_exercises as $exercise) {
                    $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $day);
                    $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description, $exercise->extra_description, $day, $exercise_arrangement, $to_program_id);
                    if ($exercise->videos()->exists()) {
                        $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
                    }
                }
                DB::commit();
            }
            $day++;
        }
        return sendResponse(['message' => "Exercise days copied successfully"]);
    }

    function delete_days($request)
    {
        $this->validationServices->delete_program_exercise_days($request);
        $program_id = $request['program_id'];
        $deleted_days = $request['deleted_days'];
        foreach ($deleted_days as $day) {
            $program_exercises = $this->DB_Exercises->get_program_exercises_by_day(program_id: $program_id, day: $day);

            DB::beginTransaction();
            if ($program_exercises) {
                foreach ($program_exercises as $exercise) {
                    if ($exercise->videos()->exists()) {
                        //delete exercises videos
                        $exercise->videos()->delete();
                    }
                    //delete exercises
                    $exercise->delete();
                }
            }
            DB::commit();
        }
        return sendResponse(['message' => "Exercise days deleted successfully"]);
    }

    public function update($request)
    {
        $this->validationServices->edit_program_exercise($request);
        $exercise_id = $request['exercise_id'];
        $name = $request['name'];
        $description = $request['description'];
        $extra_description = $request['extra_description'];
        $order = $request['order'];
        $videos = $request['videos'];
        $exercise = $this->DB_Exercises->find_exercise($exercise_id);

        $this->DB_Exercises->update_exercise($exercise, $name, $description, $extra_description, $order);
        $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
        $this->add_exercises_videos($exercise_id, $videos);
//        rearrange the exercises
        $other_exercises = $this->DB_Exercises->get_other_exercises($exercise->program_id, $exercise->day, $exercise_id);
        $this->rearrange_program_exercises($other_exercises, $order);
        return sendResponse(['message' => "Exercise updated successfully"]);

    }

    public function destroy($request)
    {
        $this->validationServices->delete_program_exercise($request);

        $exercise_id = $request['exercise_id'];

        $exercise = $this->DB_Exercises->find_exercise($exercise_id);
        $other_exercises = $this->DB_Exercises->get_other_exercises($exercise->program_id, $exercise->day, $exercise_id);
        $this->rearrange_program_exercises($other_exercises, "0");
        $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
        $this->DB_Exercises->delete_single_exercises($exercise_id);

        return sendResponse(['message' => "Exercise deleted successfully"]);
    }

    private function add_exercises_videos($exercise_id, mixed $videos)
    {
        if (!is_null($videos)) {
            foreach ($videos as $video) {
                $this->DB_ProgramExerciseVideos->create_program_exercise_video($exercise_id, $video);
            }
        }
    }

    private function list_program_exercises_arr(Collection|array $program_exercises)
    {
        $program_exercises_arr = [];
        if ($program_exercises) {
            foreach ($program_exercises as $day => $day_exercises) {
                foreach ($day_exercises as $exercise) {
                    $single_program_exercises_arr = $this->program_exercises_arr($exercise);
                    $program_exercises_arr[] = $single_program_exercises_arr;
                }
            }
        }
        return $program_exercises_arr;
    }

    private function program_exercises_arr(mixed $exercise)
    {
        $single_program_exercises_arr = [];
        $single_program_exercises_arr['id'] = $exercise->id;
        $single_program_exercises_arr['arrangement'] = $exercise->arrangement;
        $single_program_exercises_arr['name'] = $exercise->name;
        $single_program_exercises_arr['description'] = $exercise->description;
        $single_program_exercises_arr['extra_description'] = $exercise->extra_description;
        $single_program_exercises_arr['day'] = $exercise->day;
        $single_program_exercises_arr['videos'] = [];
        if ($exercise->videos()->exists()) {
            foreach ($exercise->videos as $video) {
                $single_video_arr = [];
                $single_video_arr['title'] = $video->title;
                $single_video_arr['link'] = $video->link;
                $single_program_exercises_arr['videos'][] = $single_video_arr;
            }
        }
        return $single_program_exercises_arr;
    }

    private function list_program_exercises_by_day_arr(Collection|array $program_exercises)
    {
        $program_exercises_arr = [];
        if ($program_exercises) {
            foreach ($program_exercises as $exercise) {
                $single_program_exercises_arr = $this->program_exercises_arr($exercise);
                $program_exercises_arr[] = $single_program_exercises_arr;
            }
        }
        return $program_exercises_arr;
    }

    private function get_week_arr(mixed $week)
    {
        $days_arr = [];
        if ($week) {
            $start_day = ($week * 7) - 6;
            $end_day = $start_day + 7;
            for ($i = $start_day; $i < $end_day; $i++) {
                $days_arr[] = $i;
            }
        }
        return $days_arr;

    }
}
