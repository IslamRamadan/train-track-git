<?php

namespace App\Services\DatabaseServices;

use App\Models\OtoExerciseComment;

class DB_OtoExerciseComments
{
    public function create_comment($date, $comment, $sender, $oto_program_id)
    {
        OtoExerciseComment::query()->create([
            'date' => $date,
            'comment' => $comment,
            'sender' => $sender,
            'oto_program_id' => $oto_program_id,
        ]);
    }

    public function delete($comment_id)
    {
        OtoExerciseComment::query()->where('id', $comment_id)->delete();
    }
}
