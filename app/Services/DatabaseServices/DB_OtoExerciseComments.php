<?php

namespace App\Services\DatabaseServices;

use App\Models\OtoExerciseComment;
use Illuminate\Support\Collection;

class DB_OtoExerciseComments
{
    public function create_comment($date, $comment, $sender, $oto_program_id)
    {
        return OtoExerciseComment::query()->create([
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

    public function get_comments_in_date(mixed $date, $program_id = null, $client_id = null)
    {
        return OtoExerciseComment::query()
            ->with(['program.coach', 'program.client'])
            ->where('date', $date)
            ->when($program_id != null, function ($q) use ($program_id) {
                $q->where('oto_program_id', $program_id);
            })
            ->when($client_id != null, function ($q) use ($client_id) {
                $q->whereHas('program', function ($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                });
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

    /**
     * Fetch comments made on the same date by the coach
     * @param $coachId
     * @param $date
     * @return Collection
     */
    public function getProgramsWithDatesThatHasCommentsInDate($coachId, $date): Collection
    {
        return OtoExerciseComment::query()
            ->whereDate('created_at', $date)
            ->whereHas('program', function ($q1) use ($coachId) {
                $q1->where('coach_id', $coachId);
            })
            ->get()
            ->groupBy('oto_program_id')
            ->mapWithKeys(function ($group, $programId) {
                // Extract unique comment dates per program
                return [$programId => $group->pluck('date')->unique()->values()->toArray()];
            });
    }

    /**
     * Fetch all comments again for the same program/date pairs
     * @param $programs
     * @return mixed
     */
    public function getCommentsForProgramDatePairs($programs)
    {
        return OtoExerciseComment::where(function ($query) use ($programs) {
            foreach ($programs as $programId => $dates) {
                $query->orWhere(function ($subQuery) use ($programId, $dates) {
                    $subQuery->where('oto_program_id', $programId)
                        ->whereIn('date', $dates);
                });
            }
        })->get()
            ->groupBy(function ($comment) {
                // Key: programId_date
                return $comment->oto_program_id . '_' . $comment->date;
            });
    }
}
