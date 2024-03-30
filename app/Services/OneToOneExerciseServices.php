<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OneToOneProgramExerciseVideos;
use App\Services\DatabaseServices\DB_OtoExerciseComments;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OneToOneExerciseServices
{
    public function __construct(protected ValidationServices $validationServices
        , protected DB_OneToOneProgramExercises              $DB_OneToOneProgramExercises
        , protected DB_OneToOneProgramExerciseVideos         $DB_OneToOneProgramExerciseVideos
        , protected DB_ExerciseLog                           $DB_ExerciseLog, protected DB_OtoExerciseComments $DB_OtoExerciseComments
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
            $this->DB_OneToOneProgramExercises->update_exercise($exercise, $exercise->name, $exercise->description, $exercise->extra_description, $current_order);
            $current_order++;
        }
    }

    public function list_client_exercises($request)
    {
        $this->validationServices->list_client_exercises($request);
        $program_id = $request['client_program_id'];
        $program_exercises = $this->DB_OneToOneProgramExercises->get_program_exercises($program_id);
        $program_exercises_arr = $this->list_program_exercises_arr($program_exercises, $program_id);

        return sendResponse($program_exercises_arr);
    }

    public function list_client_program_exercises_by_date($request)
    {
        $this->validationServices->list_client_program_exercises_by_date($request);
        $program_id = $request['client_program_id'];
        $date = $request['date'];
        $program_exercises = $this->DB_OneToOneProgramExercises->get_program_exercises_by_date($program_id, $date);
//        $program_exercises_arr = $this->list_program_exercises_by_day_arr($program_exercises);
        $program_exercises_arr = [];
        if ($program_exercises) {
            foreach ($program_exercises as $exercise) {
                $single_program_exercises_arr = [];
                $single_program_exercises_arr['id'] = $exercise->id;
                $single_program_exercises_arr['arrangement'] = $exercise->arrangement;
                $single_program_exercises_arr['name'] = $exercise->name;
                $single_program_exercises_arr['description'] = $exercise->description;
                $single_program_exercises_arr['extra_description'] = $exercise->extra_description;
                $single_program_exercises_arr['is_done'] = $exercise->is_done;
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
                    $single_log_arr['sets'] = $exercise->log->sets;
                    $single_log_arr['details'] = $exercise->log->details;
                    $single_program_exercises_arr['logs'][] = $single_log_arr;
                }
                $program_exercises_arr[] = $single_program_exercises_arr;
            }
        }
        $exercises_arr['exercises'] = $program_exercises_arr;
        $comments_in_this_day = $this->DB_OtoExerciseComments->get_comments_in_date(date: $date, program_id: $program_id);
        $program_comments_arr = $this->date_comments($comments_in_this_day);
        $exercises_arr['comments'] = $program_comments_arr;

        return sendResponse($exercises_arr);

    }

    public function list_client_exercises_in_date($request)
    {
        $this->validationServices->list_client_exercises_in_date($request);
        $client_id = $request->user()->id;
        $date = $request['date'];
        $exercises = $this->DB_OneToOneProgramExercises->get_client_exercises_by_date($client_id, $date);
        $done_exercises_count = $this->DB_OneToOneProgramExercises->get_done_client_exercises_by_date($client_id, $date);
        $exercises_arr = [];
        $exercises_arr['total_exercises'] = count($exercises);
        $exercises_arr['completed_exercises'] = $done_exercises_count;
        $program_exercises_arr = [];
        if ($exercises) {
            foreach ($exercises as $exercise) {
                $single_program_exercises_arr = [];
                $single_program_exercises_arr['program_id'] = $exercise->one_to_one_program->id;
                $single_program_exercises_arr['program_name'] = $exercise->one_to_one_program->name;
                $single_program_exercises_arr['exercise_id'] = $exercise->id;
                $single_program_exercises_arr['exercise_arrangement'] = $exercise->arrangement;
                $single_program_exercises_arr['exercise_name'] = $exercise->name;
                $single_program_exercises_arr['exercise_description'] = $exercise->description;
                $single_program_exercises_arr['exercise_extra_description'] = $exercise->extra_description;
                $single_program_exercises_arr['exercise_is_done'] = $exercise->is_done;
                $logs_arr = [];
                if ($exercise->log) {
                    $logs_arr['id'] = $exercise->log->id;
                    $logs_arr['sets'] = $exercise->log->sets;
                    $logs_arr['details'] = $exercise->log->details;
                    $single_program_exercises_arr['log'] = [$logs_arr];
                } else {
                    $single_program_exercises_arr['log'] = $logs_arr;
                }
                $single_program_exercises_arr['videos'] = [];
                if ($exercise->videos()->exists()) {
                    foreach ($exercise->videos as $video) {
                        $single_video_arr = [];
                        $single_video_arr['title'] = $video->title;
                        $single_video_arr['link'] = $video->link;
                        $single_program_exercises_arr['videos'][] = $single_video_arr;
                    }
                }
                $program_exercises_arr[] = $single_program_exercises_arr;
            }
        }
        $exercises_arr['exercises'] = $program_exercises_arr;
        $comments_in_this_day = $this->DB_OtoExerciseComments->get_comments_in_date(date: $date);
        $program_comments_arr = $this->date_comments($comments_in_this_day);
        $exercises_arr['comments'] = $program_comments_arr;

        return sendResponse($exercises_arr);
    }

    public function add_client_exercise($request)
    {
        $this->validationServices->add_client_program_exercise($request);
        $program_id = $request['client_program_id'];
        $name = $request['name'];
        $date = $request['date'];
        $description = $request['description'];
        $extra_description = $request['extra_description'];
        $videos = $request['videos'];

        $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement($program_id, $date);

        DB::beginTransaction();
        $exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($name, $description, $extra_description, $date, $exercise_arrangement, $program_id);
        $this->add_exercises_videos($exercise->id, $videos);
        DB::commit();

        return sendResponse(['exercise_id' => $exercise->id, 'message' => "Exercise added successfully"]);
    }

    public function copy_client_exercise($request)
    {
        $this->validationServices->copy_client_program_exercise($request);
        $exercise_id = $request['client_exercise_id'];
        $to_client_program_id = $request['to_client_program_id'];
        $date = $request['date'];
        $exercise = $this->DB_OneToOneProgramExercises->find_exercise($exercise_id);
        $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement(program_id: $to_client_program_id, date: $date);
        DB::beginTransaction();
        $copied_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($exercise->name, $exercise->description, $exercise->extra_description, $date, $exercise_arrangement, $to_client_program_id);
        if ($exercise->videos()->exists()) {
            $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
        }
        DB::commit();

        return sendResponse(['exercise_id' => $copied_exercise->id, 'message' => "Exercise copied successfully"]);
    }

    function copy_client_exercise_days($request)
    {
        $this->validationServices->copy_client_program_exercise_days($request);
        $from_client_program_id = $request['from_client_program_id'];
        $to_client_program_id = $request['to_client_program_id'];
        $copied_dates = $request['copied_dates'];
        $date = $request['start_date'];

        foreach ($copied_dates as $copied_date) {
            $day_exercises = $this->DB_OneToOneProgramExercises->get_program_exercises_by_date(program_id: $from_client_program_id, date: $copied_date);
            if ($day_exercises) {
                DB::beginTransaction();
                foreach ($day_exercises as $exercise) {
                    $exercise_arrangement = $this->DB_OneToOneProgramExercises->get_exercise_arrangement($to_client_program_id, $date);
                    $copied_exercise = $this->DB_OneToOneProgramExercises->add_oto_exercise($exercise->name, $exercise->description, $exercise->extra_description, $date, $exercise_arrangement, $to_client_program_id);
                    if ($exercise->videos()->exists()) {
                        $this->add_exercises_videos($copied_exercise->id, $exercise->videos);
                    }
                }
                DB::commit();
            }
            $date = Carbon::parse($date)->addDay()->toDateString();
        }
        return sendResponse(['message' => "Exercise days copied successfully"]);
    }

    public function update_client_exercise($request)
    {
        $this->validationServices->edit_client_program_exercise($request);

        $exercise_id = $request['client_exercise_id'];
        $name = $request['name'];
        $description = $request['description'];
        $extra_description = $request['extra_description'];
        $videos = $request['videos'];
        $order = $request['order'];
        $exercise = $this->DB_OneToOneProgramExercises->find_exercise($exercise_id);

        $this->DB_OneToOneProgramExercises->update_exercise($exercise, $name, $description, $extra_description, $order);
        $this->DB_OneToOneProgramExerciseVideos->delete_exercise_videos($exercise);
        $this->add_exercises_videos($exercise_id, $videos);
//        rearrange the exercises
        $other_exercises = $this->DB_OneToOneProgramExercises->get_other_exercises($exercise->one_to_one_program_id, $exercise->date, $exercise_id);
        $this->rearrange_program_exercises($other_exercises, $order);
        return sendResponse(['message' => "Exercise updated successfully"]);
    }


    public function delete_client_exercise($request)
    {
        $this->validationServices->delete_client_exercise($request);

        $exercise_id = $request['client_exercise_id'];

        $exercise = $this->DB_OneToOneProgramExercises->find_exercise($exercise_id);
        $other_exercises = $this->DB_OneToOneProgramExercises->get_other_exercises($exercise->one_to_one_program_id, $exercise->date, $exercise_id);
        $this->rearrange_program_exercises($other_exercises, "0");

        $this->DB_OneToOneProgramExerciseVideos->delete_exercise_videos($exercise);
        $this->DB_ExerciseLog->delete_exercise_log($exercise);
        $this->DB_OneToOneProgramExercises->delete_single_exercises($exercise_id);

        return sendResponse(['message' => "Exercise deleted successfully"]);
    }

    public function log_client_exercise($request)
    {
        $this->validationServices->log_client_exercise($request);
        $client_id = $request->user()->id;
        $client_exercise_id = $request->client_exercise_id;
        $sets = $request->sets;
        $details = $request->details;
        $exercise_log = $this->DB_ExerciseLog->find_exercise_log(exercise_id: $client_exercise_id);
        if ($exercise_log) {
            $this->DB_ExerciseLog->update_exercise_log($exercise_log->id, $sets, $details);
        } else {
            $this->DB_ExerciseLog->create_exercise_log($client_exercise_id, $sets, $details, $client_id);
        }
        $this->DB_OneToOneProgramExercises->update_exercise_status($client_exercise_id, "1");
        return sendResponse(['message' => "Log created successfully"]);
    }

    public function log_client_exercise_update($request)
    {
        $this->validationServices->log_client_exercise_update($request);
        $log_id = $request->log_id;
        $sets = $request->sets;
        $details = $request->details;
        $this->DB_ExerciseLog->update_exercise_log($log_id, $sets, $details);
        return sendResponse(['message' => "Log updated successfully"]);
    }

    public function update_exercise_status($request)
    {
        $this->validationServices->update_exercise_status($request);
        $client_exercise_id = $request->client_exercise_id;
        $status = $request->status;

        $this->DB_OneToOneProgramExercises->update_exercise_status($client_exercise_id, $status);
        return sendResponse(['message' => "exercise status successfully"]);
    }

    private function add_exercises_videos($exercise_id, mixed $videos)
    {
        if (!is_null($videos)) {
            foreach ($videos as $video) {
                $this->DB_OneToOneProgramExerciseVideos->create_program_exercise_video($exercise_id, $video);
            }
        }
    }

    private function list_program_exercises_arr(Collection|array $program_exercises, $program_id)
    {
        $program_exercises_arr = [];
        if ($program_exercises) {
            $single_day = [];

            foreach ($program_exercises as $date => $day_exercises) {
                $single_day['date'] = $date;
                $single_exercise = [];
                foreach ($day_exercises as $exercise) {
                    $single_program_exercises_arr = $this->program_exercises_arr($exercise);
                    $single_exercise[] = $single_program_exercises_arr;
                }
                $single_day['exercises'] = $single_exercise;
                $comments_in_this_day = $this->DB_OtoExerciseComments->get_comments_in_date(date: $date, program_id: $program_id);
                $program_comments_arr = $this->date_comments($comments_in_this_day);
                $single_day['comments'] = $program_comments_arr;
                $program_exercises_arr[] = $single_day;
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
        $single_program_exercises_arr['date'] = $exercise->date;
        $single_program_exercises_arr['is_done'] = $exercise->is_done;
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
            $single_log_arr['sets'] = $exercise->log->sets;
            $single_log_arr['details'] = $exercise->log->details;
            $single_program_exercises_arr['logs'][] = $single_log_arr;
        }
        return $single_program_exercises_arr;
    }

//    private function list_program_exercises_by_day_arr(Collection|array $program_exercises)
//    {
//        $program_exercises_arr = [];
//        if ($program_exercises) {
//            foreach ($program_exercises as $exercise) {
//                $single_program_exercises_arr = $this->program_exercises_arr($exercise);
//                $program_exercises_arr[] = $single_program_exercises_arr;
//            }
//        }
//        return $program_exercises_arr;
//    }

    /**
     * @param Collection|array $comments_in_this_day
     * @param array $exercises_arr
     * @return array
     */
    private function date_comments(Collection|array $comments_in_this_day): array
    {
        $program_comments_arr = [];
        if ($comments_in_this_day) {
            foreach ($comments_in_this_day as $comment) {
                $single_program_comments_arr = [];
                $single_program_comments_arr['comment_id'] = $comment->id;
                $single_program_comments_arr['comment_content'] = $comment->comment;
                $single_program_comments_arr['sender'] = $comment->user_type;
                $single_program_comments_arr['coach_id'] = $comment->program->coach_id;
                $single_program_comments_arr['coach_name'] = $comment->program->coach->name;
                $single_program_comments_arr['client_id'] = $comment->program->client_id;
                $single_program_comments_arr['client_name'] = $comment->program->client->name;

                $program_comments_arr[] = $single_program_comments_arr;
            }
        }
        return $program_comments_arr;
    }

}
