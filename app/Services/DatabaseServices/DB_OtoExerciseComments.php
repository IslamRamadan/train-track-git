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

    public function delete_date_comments($date, $program_id)
    {
        OtoExerciseComment::query()->where(['date' => $date, 'oto_program_id' => $program_id])->delete();
    }

    public function get_comments_in_date(mixed $date, $program_id = null)
    {
        return OtoExerciseComment::query()
            ->with(['program.coach', 'program.client'])
            ->where('date', $date)
            ->when($program_id != null, function ($q) use ($program_id) {
                $q->where('oto_program_id', $program_id);
            })
            ->get();
    }

    public function get_client_comments_in_date(mixed $date, $client_id)
    {
        return OtoExerciseComment::query()
            ->with(['program.coach', 'program.client'])
            ->where('date', $date)
            ->whereHas('program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->get();
    }
}
