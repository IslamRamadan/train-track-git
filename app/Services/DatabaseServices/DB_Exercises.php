<?php

namespace App\Services\DatabaseServices;

use App\Models\ProgramExercise;

class DB_Exercises
{
    public function add_exercise($name, $description, $extra_description, $day, $arrangement, $program_id)
    {
        return ProgramExercise::create([
            'name' => $name,
            'description' => $description,
            'extra_description' => $extra_description,
            'day' => $day,
            'arrangement' => $arrangement,
            'program_id' => $program_id,
        ]);
    }

    public function get_exercise_arrangement(mixed $program_id, mixed $day)
    {
        $get_last_exercise_arrangement = ProgramExercise::query()
            ->where(['program_id' => $program_id, 'day' => $day])
            ->orderBy('arrangement', 'DESC')
            ->first();
        return $get_last_exercise_arrangement ? $get_last_exercise_arrangement->arrangement + 1 : 1;
    }

    public function get_program_exercises(mixed $program_id, $days)
    {
        return ProgramExercise::with('videos')
            ->where('program_id', $program_id)
            ->when(!empty($days), function ($q) use ($days) {
                $q->whereIn('day', $days);
            })
            ->orderBy('day')
            ->get()->groupBy('day');
    }

    public function get_program_exercises_by_day(mixed $program_id, $day, $copied_exercises_arr = [])
    {
        return ProgramExercise::with(['videos', 'program'])
            ->where(['program_id' => $program_id, 'day' => $day])
            ->whereNotIn('id', $copied_exercises_arr)
            ->orderBy('arrangement')
            ->get();
    }

    public function get_program_exercises_days(mixed $program_id)
    {
        return ProgramExercise::query()->select('day')->where(['program_id' => $program_id])
            ->orderBy('day')->distinct()->pluck('day');
    }

    public function get_program_exercises_day_sorted(mixed $program_id, $start_day, $end_day)
    {
        return ProgramExercise::query()
            ->where('program_id', $program_id)
            ->where('day', '>=', $start_day)
            ->when($end_day != "", function ($q) use ($end_day) {
                $q->where('day', '<=', $end_day);
            })
            ->orderBy('day')->get();
    }

    public function find_exercise($exercise_id)
    {
        return ProgramExercise::query()->with(['videos', 'program'])->find($exercise_id);
    }

    public function update_exercise($exercise, $name, $description, $extra_description, $order)
    {
        $exercise->update([
            'name' => $name,
            'description' => $description,
            'extra_description' => $extra_description,
            'arrangement' => $order,
        ]);
    }

    public function get_other_exercises(mixed $program_id, mixed $day, mixed $exercise_id)
    {
        return ProgramExercise::query()->where(['program_id' => $program_id, 'day' => $day])
            ->where('id', '!=', $exercise_id)->orderBy('arrangement')->get();
    }

    public function delete_program_exercises($exercise)
    {
        return $exercise->delete();
    }

    public function delete_single_exercises(mixed $id)
    {
        return ProgramExercise::query()->where('id', $id)->delete();
    }
    public function verify_exercise_id(mixed $id)
    {
        return ProgramExercise::query()->where('id', $id)->exists();
    }

}
