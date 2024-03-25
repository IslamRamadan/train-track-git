<?php

namespace App\Services\DatabaseServices;

use App\Models\OtoProgramExerciseVideo;

class DB_OneToOneProgramExerciseVideos
{

    public function create_program_exercise_video($exercise_id, mixed $video)
    {
        return OtoProgramExerciseVideo::query()->create([
            'title' => $video['title'],
            'link' => $video['link'],
            'oto_program_exercise_id' => $exercise_id,
        ]);
    }

    public function delete_exercise_videos($exercise)
    {
        return $exercise->videos()->delete();
    }
}
