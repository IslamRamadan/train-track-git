<?php

namespace App\Services\DatabaseServices;

use App\Models\CoachExerciseTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DB_CoachExerciseTemplates
{
    /**
     * @param mixed $coach_id
     * @param mixed $search
     * @return Collection
     */
    public function get_exercise_templates(mixed $coach_id, mixed $search): Collection
    {
        return CoachExerciseTemplate::query()
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%');;
            })
            ->where('coach_id', $coach_id)
            ->get();
    }

    /**
     * @param mixed $coach_id
     * @param $title
     * @param $description
     * @return Builder|Model
     */
    public function add_exercise_template(mixed $coach_id, $title, $description): Model|Builder
    {
        return CoachExerciseTemplate::query()->create([
            'coach_id' => $coach_id,
            'title' => $title,
            'description' => $description,
        ]);
    }


    public function edit_exercise_template(mixed $exercise_template_id, mixed $description, mixed $title)
    {
        return CoachExerciseTemplate::query()
            ->where('id', $exercise_template_id)
            ->update([
                'description' => $description,
                'title' => $title,
            ]);
    }

    public function delete_exercise_template($exercise_template_id)
    {
        return CoachExerciseTemplate::query()
            ->where('id', $exercise_template_id)->delete();
    }

    public function verify_coach_id($coach_id, $exercise_template_id)
    {
        return CoachExerciseTemplate::query()
            ->where([
                'coach_id' => $coach_id,
                'id' => $exercise_template_id
            ])
            ->exists();
    }
}
