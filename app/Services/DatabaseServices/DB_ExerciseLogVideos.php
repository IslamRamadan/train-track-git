<?php

namespace App\Services\DatabaseServices;

use App\Models\ExerciseLogVideo;

class DB_ExerciseLogVideos
{
    public function delete_exercise_log_videos($exercise_log)
    {
        $exercise_log->log_videos()->delete();
    }

    public function create_exercise_log_videos($exercise_log_id, $path)
    {
        ExerciseLogVideo::query()->create([
            'exercise_log_id' => $exercise_log_id,
            'path' => $path
        ]);
    }
}
