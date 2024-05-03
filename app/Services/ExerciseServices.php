<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_ProgramExerciseVideos;
use App\Services\DatabaseServices\DB_Programs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExerciseServices
{
    public function __construct(protected ValidationServices               $validationServices,
                                protected DB_Programs                      $DB_Programs,
                                protected DB_Exercises                     $DB_Exercises,
                                protected DB_ProgramClients                $DB_ProgramClients,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected DB_ProgramExerciseVideos         $DB_ProgramExerciseVideos
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
        $sync = $request['sync'];
        $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($program_id, $day);
        DB::beginTransaction();
        $exercise = $this->DB_Exercises->add_exercise($name, $description, $extra_description, $day, $exercise_arrangement, $program_id);
        $this->add_exercises_videos($exercise->id, $videos);
        if ($sync == "1") {
            $program = $this->DB_Programs->find_program($program_id);
            $sync_date = $this->get_date_after_n_days(starting_date: $program->starting_date, number_of_days_after_starting: $day - 1);
            // get the programs related to this template program
            $related_programs = $this->DB_ProgramClients->get_program_related_oto_programs($program_id);

            if (count($related_programs) > 0) {
                foreach ($related_programs as $oto_program) {
                    $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement($oto_program->oto_program_id,
                        $sync_date);
                    $oto_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($name, $description, $extra_description,
                        $sync_date, $exercise_arrangement, $oto_program->oto_program_id);
                    $this->add_oto_exercises_videos($oto_exercise->id, $videos);
                }
            }
        }
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

        $copied_days_arr = $this->make_copied_days_arr($copied_days);//define which day that will be copied and which day will not

        $day = $request['start_day'];
        $this->copy_days_logic(days_arr: $copied_days_arr, from_program_id: $from_program_id, to_program_id: $to_program_id, start_day: $day);
        return sendResponse(['message' => "Exercise days copied successfully"]);
    }


    function cut_days($request)
    {
        $this->validationServices->cut_program_exercise_days($request);
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];
        $cut_days = $request['cut_days'];
        $start_day = $request['start_day'];

        $cut_days_arr = $this->make_copied_days_arr($cut_days);//define which day that will be cut and which day will not

        $this->copy_days_logic(days_arr: $cut_days_arr, from_program_id: $from_program_id, to_program_id: $to_program_id,
            start_day: $start_day, operation_type: "cut");

        return sendResponse(['message' => "Exercise days cut successfully"]);
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

    private function add_oto_exercises_videos($exercise_id, mixed $videos)
    {
        if (!is_null($videos)) {
            foreach ($videos as $video) {
                $this->DB_OneToOneProgramExerciseVideos->create_program_exercise_video($exercise_id, $video);
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

    private function make_copied_days_arr(mixed $copied_days)
    {
        $result = [];
        // Get the first item using its index (0 for the first element)
        $first_item = intval($copied_days[0]);

        // Get the last item using its index (array length - 1)
        $last_item = $copied_days[count($copied_days) - 1];
        for ($i = $first_item; $i <= $last_item; $i++) {
            $single_day['day'] = $i;
            if (in_array($i, $copied_days)) {
                $single_day['copy'] = true;
            } else {
                $single_day['copy'] = false;
            }
            $result[] = $single_day;
        }
        return $result;
    }

    /**
     * @param $days_arr
     * @param mixed $from_program_id
     * @param mixed $to_program_id
     * @param int $start_day
     * @param string $operation_type
     * @return void
     */
    private function copy_days_logic($days_arr, mixed $from_program_id, mixed $to_program_id, int $start_day, string $operation_type = "copy"): void
    {
        foreach ($days_arr as $single_day) {
            if ($single_day['copy']) {
                $day_exercises = $this->DB_Exercises->get_program_exercises_by_day(program_id: $from_program_id,
                    day: $single_day['day']);
                if ($day_exercises) {
//                    DB::beginTransaction();
                    foreach ($day_exercises as $exercise) {
                        $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $start_day);
                        Log::info($exercise_arrangement);
                        $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description,
                            $exercise->extra_description, $start_day, $exercise_arrangement, $to_program_id);
                        if ($exercise->videos()->exists()) {
                            $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
                        }
                        if ($operation_type == "cut") {
                            $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
                            $this->DB_Exercises->delete_single_exercises($exercise->id);
                        }
                    }
//                    DB::commit();
                }
            }
            $start_day++;
        }
    }

    private function get_date_after_n_days($starting_date, $number_of_days_after_starting)
    {
        return Carbon::parse($starting_date)->addDays($number_of_days_after_starting)->toDateString();
    }
}
