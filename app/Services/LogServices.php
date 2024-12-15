<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_ExerciseLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class LogServices
{
    public function __construct(protected ValidationServices $validationServices, protected DB_ExerciseLog $DB_ExerciseLog)
    {
    }

    public function client_logs_list($request)
    {
        $client_id = $request->user()->id;
        $logs = $this->DB_ExerciseLog->list_cient_logs($client_id);
        return $this->list_logs_arr($logs);
    }

    public function client_programs_logs_list($request)
    {
        $this->validationServices->client_programs_logs_list($request);
        $client_id = $request->client_id ?: $request->user()->id;
        $program_id = $request->client_program_id;
        $logs = $this->DB_ExerciseLog->list_cient_program_logs($client_id, $program_id);
        return $this->list_logs_arr($logs);
    }

    /**
     * @param Collection|array $logs
     * @return JsonResponse
     */
    public function list_logs_arr(Collection|array $logs): JsonResponse
    {
        $logs_arr = [];
        if ($logs) {
            foreach ($logs as $log) {
                $single_log_arr = [];
                $single_log_arr['program_id'] = $log->exercise->one_to_one_program->id;
                $single_log_arr['program_name'] = $log->exercise->one_to_one_program->name;
                $single_log_arr['exercise_id'] = $log->exercise->id;
                $single_log_arr['exercise_name'] = $log->exercise->name;
                $single_log_arr['exercise_description'] = $log->exercise->description;
                $single_log_arr['log_id'] = $log->id;
                $single_log_arr['log_sets'] = "0";
                $single_log_arr['log_details'] = $log->details;
                $single_log_arr['log_date'] = $log->created_at->format("Y-m-d");
                $single_log_arr['log_time'] = $log->created_at->format("H:i:s");
                $single_log_arr['videos'] = [];

                if ($log->log_videos()->exists()) {
                    foreach ($log->log_videos as $log_video) {
                        $single_log_arr['videos'][] = $log_video->path;
                    }
                }
                $logs_arr[] = $single_log_arr;
            }
        }
        return sendResponse($logs_arr);
    }


}
