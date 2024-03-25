<?php

namespace App\Services\DatabaseServices;

use App\Models\ProgramExerciseVideo;

class DB_ProgramExerciseVideos
{

    public function create_program_exercise_video($exercise_id, mixed $video)
    {
        return ProgramExerciseVideo::query()->create([
            'title' => $video['title'],
            'link' => $video['link'],
            'program_exercise_id' => $exercise_id,
        ]);
    }

    public function delete_exercise_videos($exercise)
    {
        return $exercise->videos()->delete();
    }
}
