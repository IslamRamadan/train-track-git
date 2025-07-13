<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_ExerciseLogVideos;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_OtoExerciseComments;
use App\Services\DatabaseServices\DB_ProgramClients;
use App\Services\DatabaseServices\DB_ProgramExerciseVideos;
use App\Services\DatabaseServices\DB_Programs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VVExerciseServices
{
    public function __construct(protected ValidationServices               $validationServices,
                                protected DB_Programs                      $DB_Programs,
                                protected DB_Exercises                     $DB_Exercises,
                                protected DB_ProgramClients                $DB_ProgramClients,
                                protected DB_OneToOneProgramExercises      $DB_OneToOneProgramExercises,
                                protected DB_OtoExerciseComments           $DB_OtoExerciseComments,
                                protected DB_ExerciseLog                   $DB_ExerciseLog,
                                protected DB_OneToOneProgramExerciseVideos $DB_OneToOneProgramExerciseVideos,
                                protected DB_ProgramExerciseVideos         $DB_ProgramExerciseVideos,
                                protected DB_ExerciseLogVideos             $DB_ExerciseLogVideos
    )
    {
    }

    /**
     * from_type -> ['template','oto']
     * to_type -> ['template','oto']
     * ex_id
     * to_program_id
     * day required if to_type is template
     * date required if to_type is oto
     */
    public function copy($request)
    {
        $this->validationServices->copy_vv_program_exercise($request);
        $from_type = $request['from_type'];
        $to_type = $request['to_type'];
        $exercise_id = $request['exercise_id'];
        $to_program_id = $request['to_program_id'];
        $day = $request['to_day'];
        $date = $request['to_date'];
        if ($from_type == "template") {
            $exercise = $this->DB_Exercises->find_exercise($exercise_id);
            $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement(program_id: $to_program_id, date: $date);
        }
        if ($from_type == "oto") {
            $exercise = $this->DB_OneToOneProgramExercises->find_exercise($exercise_id);
            $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $day);
        }
        DB::beginTransaction();
        if ($to_type == "template") {
            $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description, $exercise->extra_description, $day, $exercise_arrangement, $to_program_id);
            if ($exercise->videos()->exists()) {
                $this->add_template_exercises_videos($copied_exercise->id, $exercise->videos);
                if ($copied_exercise->program->sync == "1") {
                    $this->sync_on_add_exercise($copied_exercise->program->starting_date, $day, $to_program_id, $exercise->name,
                        $exercise->description, $exercise->extra_description, $copied_exercise->id, $exercise->videos);
                }
                $exercise_arr = $this->program_exercises_arr($copied_exercise, $copied_exercise->program->starting_date);
            }
        }
        if ($to_type == "oto") {
            $copied_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($exercise->name, $exercise->description, $exercise->extra_description, $date, $exercise_arrangement, $to_program_id);
            if ($exercise->videos()->exists()) {
                $this->add_oto_exercises_videos($copied_exercise->id, $exercise->videos);
            }
            $exercise_arr = $this->client_program_exercises_arr($copied_exercise);
        }
        DB::commit();

        return sendResponse(['exercise_id' => $copied_exercise->id, 'message' => "Exercise copied successfully", 'exercise' => $exercise_arr]);
    }

    function copy_days($request)
    {
        $this->validationServices->copy_vv_program_exercise_days($request);
        $from_type = $request['from_type'];
        $to_type = $request['to_type'];
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];
        $copied_days = $request['copied_days'];//from--->template
        $start_day = $request['start_day'];//from--->template
        $copied_dates = $request['copied_dates'];//from--->oto
        $start_date = $request['start_date'];//from--->template
        if ($from_type == "template") {
            $copied_days_arr = $this->make_copied_days_arr($copied_days);//define which day that will be copied and which day will not
        } else {//oto
            $copied_days_arr = $this->make_copied_dates_arr($copied_dates);//define which day that will be copied and which day will not
        }


        $copied_exercises_arr = [];
        $exercise_arr = [];
        DB::beginTransaction();
        foreach ($copied_days_arr as $single_day) {
            if ($single_day['copy']) {
                if ($from_type == "template") {
                    $day_exercises = $this->DB_Exercises->get_program_exercises_by_day(program_id: $from_program_id,
                        day: $single_day['day'], copied_exercises_arr: $copied_exercises_arr);
                } else {//oto
                    $day_exercises = $this->DB_OneToOneProgramExercises->get_program_exercises_by_date(program_id: $from_program_id, date: $single_day['day'], copied_exercises_arr: $copied_exercises_arr);
                }

                if ($day_exercises) {
                    foreach ($day_exercises as $exercise) {
                        if ($to_type == "template") {
                            $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $start_day);
                            $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description,
                                $exercise->extra_description, $start_day, $exercise_arrangement, $to_program_id);
                            $copied_exercises_arr[] = $copied_exercise->id;//add the new copied/cut exercise to avoid the cut issue
                            if ($exercise->videos()->exists()) {
                                $this->add_template_exercises_videos($copied_exercise->id, $exercise->videos);
                            }
                            if ($copied_exercise->program->sync == "1") {
                                $this->sync_on_add_exercise($copied_exercise->program->starting_date, $start_day, $to_program_id, $exercise->name,
                                    $exercise->description, $exercise->extra_description, $copied_exercise->id, $exercise->videos);
                            }
                            $exercise_arr[] = $this->program_exercises_arr($copied_exercise, $copied_exercise->program->starting_date);
                        } else {//oto
                            $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement($to_program_id, $start_date);
                            $copied_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($exercise->name,
                                $exercise->description, $exercise->extra_description, $start_date, $exercise_arrangement, $to_program_id);
                            $copied_exercises_arr[] = $copied_exercise->id;//add the new copied/cut exercise to avoid the cut issue
                            if ($exercise->videos()->exists()) {
                                $this->add_oto_exercises_videos($copied_exercise->id, $exercise->videos);
                            }
                            $exercise_arr[] = $this->client_program_exercises_arr($copied_exercise);
                        }
                    }
                }
            }
            $to_type == "template" ? $start_day++ : $start_date = Carbon::parse($start_date)->addDay()->toDateString();
        }
        DB::commit();

        return sendResponse(['message' => "Exercise days copied successfully", 'exercises' => $exercise_arr]);
    }


    function cut_days($request)
    {
        $this->validationServices->cut_program_exercise_days($request);
        $from_program_id = $request['from_program_id'];
        $to_program_id = $request['to_program_id'];
        $cut_days = $request['cut_days'];
        $start_day = $request['start_day'];

        $cut_days_arr = $this->make_copied_days_arr($cut_days);//define which day that will be cut and which day will not

        $exercise_arr = $this->copy_days_logic(days_arr: $cut_days_arr, from_program_id: $from_program_id, to_program_id: $to_program_id,
            start_day: $start_day, operation_type: "cut");

        return sendResponse(['message' => "Exercise days cut successfully", 'exercices' => $exercise_arr]);
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

    private function make_copied_dates_arr(mixed $copied_days)
    {
        $result = [];
        // Get the first item using its index (0 for the first element)
        $first_item = $copied_days[0];

        // Get the last item using its index (array length - 1)
        $last_item = $copied_days[count($copied_days) - 1];
        for ($i = $first_item; $i <= $last_item;) {
            $single_day['day'] = $i;
            if (in_array($i, $copied_days)) {
                $single_day['copy'] = true;
            } else {
                $single_day['copy'] = false;
            }
            $result[] = $single_day;
            $i = Carbon::parse($i)->addDay()->toDateString();
        }
        return $result;
    }
    /**
     * @param $days_arr
     * @param mixed $from_program_id
     * @param mixed $to_program_id
     * @param int $start_day
     * @param string $operation_type
     * @return array
     */
    private function copy_days_logic($days_arr, mixed $from_program_id, mixed $to_program_id, int $start_day, string $operation_type = "copy"): array
    {
        $copied_exercises_arr = [];
        $exercise_arr = [];
        foreach ($days_arr as $single_day) {
            Log::info("single day " . $single_day['day'] . " start");

            if ($single_day['copy']) {
                $day_exercises = $this->DB_Exercises->get_program_exercises_by_day(program_id: $from_program_id,
                    day: $single_day['day'], copied_exercises_arr: $copied_exercises_arr);
                if ($day_exercises) {
                    DB::beginTransaction();
                    foreach ($day_exercises as $exercise) {
                        Log::info("exercise $exercise->id start");

                        $exercise_arrangement = $this->DB_Exercises->get_exercise_arrangement($to_program_id, $start_day);
                        $copied_exercise = $this->DB_Exercises->add_exercise($exercise->name, $exercise->description,
                            $exercise->extra_description, $start_day, $exercise_arrangement, $to_program_id);
                        $copied_exercises_arr[] = $copied_exercise->id;//add the new copied/cut exercise to avoid the cut issue

                        if ($exercise->videos()->exists()) {
                            $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
                        }
                        if ($copied_exercise->program->sync == "1") {
                            if ($operation_type == "cut") {
                                $this->sync_on_delete_exercise($exercise->id);
                            }
                            $this->sync_on_add_exercise($copied_exercise->program->starting_date, $start_day, $to_program_id, $exercise->name,
                                $exercise->description, $exercise->extra_description, $copied_exercise->id, $exercise->videos);
                        }
                        if ($operation_type == "cut") {
                            $this->DB_ProgramExerciseVideos->delete_exercise_videos($exercise);
                            $this->DB_Exercises->delete_single_exercises($exercise->id);
                        }
                        $exercise_arr[] = $this->program_exercises_arr($copied_exercise, $copied_exercise->program->starting_date);
                    }
                    DB::commit();
                }
            }
            $start_day++;
        }
        return $exercise_arr;
    }

    private function add_template_exercises_videos($exercise_id, mixed $videos)
    {
        if (!is_null($videos)) {
            foreach ($videos as $video) {
                $this->DB_ProgramExerciseVideos->create_program_exercise_video($exercise_id, $video);
            }
        }
    }


    /**
     * @param string $program_starting_date
     * @param mixed $day
     * @param mixed $program_id
     * @param mixed $name
     * @param mixed $description
     * @param mixed $extra_description
     * @param $exercise_id
     * @param mixed $videos
     * @return void
     */
    public function sync_on_add_exercise(string $program_starting_date, mixed $day, mixed $program_id, mixed $name, mixed $description, mixed $extra_description, $exercise_id, mixed $videos): void
    {
        $sync_date = $this->get_date_after_n_days(starting_date: $program_starting_date, number_of_days_after_starting: $day - 1);
        // get the programs related to this template program
        $related_programs = $this->DB_ProgramClients->get_program_related_oto_programs($program_id);

        if (count($related_programs) > 0) {
            foreach ($related_programs as $oto_program) {
                $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement($oto_program->oto_program_id,
                    $sync_date);
                $oto_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($name, $description, $extra_description,
                    $sync_date, $exercise_arrangement, $oto_program->oto_program_id, $exercise_id);
                $this->add_oto_exercises_videos($oto_exercise->id, $videos);
            }
        }
    }

    private function get_date_after_n_days($starting_date, $number_of_days_after_starting)
    {
        return Carbon::parse($starting_date)->addDays($number_of_days_after_starting)->toDateString();
    }

    private function add_oto_exercises_videos($exercise_id, mixed $videos)
    {
        if (!is_null($videos)) {
            foreach ($videos as $video) {
                $this->DB_OneToOneProgramExerciseVideos->create_program_exercise_video($exercise_id, $video);
            }
        }
    }

    private function program_exercises_arr(mixed $exercise, $starting_date)
    {
        $single_program_exercises_arr = [];
        $single_program_exercises_arr['id'] = $exercise->id;
        $single_program_exercises_arr['arrangement'] = $exercise->arrangement;
        $single_program_exercises_arr['name'] = $exercise->name;
        $single_program_exercises_arr['description'] = $exercise->description;
        $single_program_exercises_arr['extra_description'] = $exercise->extra_description;
        $single_program_exercises_arr['day'] = $exercise->day;
        $single_program_exercises_arr['date'] = $starting_date ? $this->getCorrespondingDate($exercise->day, $starting_date) : "";
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

    private function client_program_exercises_arr(mixed $exercise)
    {
        $single_program_exercises_arr = [];
        $single_program_exercises_arr['id'] = $exercise->id;
        $single_program_exercises_arr['oto_program_id'] = $exercise->one_to_one_program_id;
        $single_program_exercises_arr['arrangement'] = strval($exercise->arrangement);
        $single_program_exercises_arr['name'] = $exercise->name;
        $single_program_exercises_arr['description'] = $exercise->description;
        $single_program_exercises_arr['extra_description'] = $exercise->extra_description;
        $single_program_exercises_arr['date'] = $exercise->date;
        $single_program_exercises_arr['is_done'] = $exercise->is_done ? $exercise->is_done : 0;
        $single_program_exercises_arr['videos'] = [];
        $single_program_exercises_arr['logs'] = [];
        if ($exercise->videos()->exists()) {
            foreach ($exercise->videos as $video) {
                $single_video_arr = [];
                $single_video_arr['title'] = $video->title;
                $single_video_arr['link'] = $video->link;
                $single_program_exercises_arr['videos'][] = $single_video_arr;
            }
        }
        if ($exercise->log()->exists()) {
            $single_log_arr = [];
            $single_log_arr['log_id'] = $exercise->log->id;
            $single_log_arr['sets'] = "0";
            $single_log_arr['videos'] = [];
            $single_log_arr['details'] = $exercise->log->details;

            if ($exercise->log->log_videos()->exists()) {
                foreach ($exercise->log->log_videos as $log_video) {
                    $single_log_arr['videos'][] = $log_video->path;
                }
            }

            $single_program_exercises_arr['logs'][] = $single_log_arr;
        }
        return $single_program_exercises_arr;
    }

    function getCorrespondingDate(int $dayNumber, string $date): string
    {
        // Parse the given date (YYYY-MM-DD)
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);

        // Calculate how many days to add
        $daysToAdd = $dayNumber - 1;

        // Add the calculated days
        $carbonDate->addDays($daysToAdd);

        // Return the modified date in YYYY-MM-DD format
        return $carbonDate->format('Y-m-d');
    }
}
