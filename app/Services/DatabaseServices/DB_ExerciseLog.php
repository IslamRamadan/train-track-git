<?php

namespace App\Services\DatabaseServices;

use App\Models\ExerciseLog;
use Carbon\Carbon;

class DB_ExerciseLog
{

    public function create_exercise_log(mixed $client_exercise_id, mixed $sets, mixed $details, mixed $client_id)
    {
        return ExerciseLog::query()->create([
            'oto_exercise_id' => $client_exercise_id,
            'sets' => $sets,
            'details' => $details,
            'client_id' => $client_id,
        ]);
    }

    public function verify_client_id($client_id, $log_id)
    {
        return ExerciseLog::query()->where(['id' => $log_id, 'client_id' => $client_id])->exists();
    }

    public function update_exercise_log(mixed $log_id, mixed $sets, mixed $details)
    {
        return ExerciseLog::query()->where('id', $log_id)->update([
            'sets' => $sets,
            'details' => $details,
        ]);
    }

    public function list_cient_logs(mixed $client_id)
    {
        return ExerciseLog::query()->with('exercise.one_to_one_program','log_videos')->where('client_id', $client_id)
            ->orderBy('created_at', "desc")->get();
    }

    public function list_cient_program_logs(mixed $client_id, $program_id)
    {
        return ExerciseLog::query()->with('exercise.one_to_one_program')
            ->whereHas('exercise', function ($query) use ($program_id) {
                $query->where('one_to_one_program_id', $program_id);
            })->
            where('client_id', $client_id)
            ->orderBy('created_at', "desc")->get();
    }

    public function delete_exercise_log(mixed $exercise)
    {
        return $exercise->log()->delete();
    }

    public function list_coach_clients_logs_today(mixed $coach_id)
    {
        return ExerciseLog::query()
            ->with(['log_videos', 'exercise.one_to_one_program.client'])
            ->whereDate('created_at', Carbon::today())
            ->whereHas('exercise', function ($query) use ($coach_id) {
            $query->whereHas('one_to_one_program', function ($userQuery) use ($coach_id) {
                $userQuery->where('coach_id', $coach_id);
            });
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function find_exercise_log($exercise_id)
    {
        return ExerciseLog::query()->with(['log_videos','exercise'])->where('oto_exercise_id', $exercise_id)->first();
    }
}
