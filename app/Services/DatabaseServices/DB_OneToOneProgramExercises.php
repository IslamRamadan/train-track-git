<?php

namespace App\Services\DatabaseServices;

use App\Models\OneToOneProgramExercise;

class DB_OneToOneProgramExercises
{

    public function check_client_has_exercises_between_two_dates($client_id, mixed $start_date, string $end_date)
    {
        return OneToOneProgramExercise::query()
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                // Use the related table's column in the condition
                $query->where('client_id', $client_id);
            })
            ->whereBetween('date', [$start_date, $end_date])->get();
    }

    public function create_one_to_one_program_exercises(mixed $exercise, mixed $exercise_date, mixed $one_to_one_program_id)
    {
        return OneToOneProgramExercise::query()->create([
            'name' => $exercise->name,
            'description' => $exercise->description,
            'extra_description' => $exercise->extra_description,
            'arrangement' => $exercise->arrangement,
            'one_to_one_program_id' => $one_to_one_program_id,
            'date' => $exercise_date,
        ]);
    }

    public function get_program_exercises(mixed $program_id, $client_id, $dates)
    {
        return OneToOneProgramExercise::query()
            ->when($program_id != null, function ($q) use ($program_id) {
                $q->where('one_to_one_program_id', $program_id);
            })
            ->when($client_id != null, function ($q) use ($client_id) {
                $q->whereHas('one_to_one_program', function ($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                });
            })
            ->when(!empty($dates), function ($q) use ($dates) {
                $q->whereIn('date', $dates);
            })
            ->orderBy('date')
            ->get()->groupBy('date');
    }

    public function get_program_exercises_by_date(mixed $program_id, $date)
    {
        return OneToOneProgramExercise::query()->where(['one_to_one_program_id' => $program_id, 'date' => $date])->orderBy('arrangement')->get();
    }

    public function get_client_exercises_by_date(mixed $client_id, $date)
    {
        return OneToOneProgramExercise::query()
            ->with(['one_to_one_program', 'log'])
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->where(['date' => $date])->orderBy('arrangement')->get();
    }

    public function get_done_client_exercises_by_date(mixed $client_id, $date)
    {
        return OneToOneProgramExercise::query()
            ->with('one_to_one_program')
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->where(['date' => $date, 'is_done' => "1"])->count();
    }

    public function get_exercise_arrangement(mixed $program_id, mixed $date)
    {
        $get_last_exercise_arrangement = OneToOneProgramExercise::where(['one_to_one_program_id' => $program_id, 'date' => $date])->orderBy('arrangement', "DESC")->latest()->first();
        return $get_last_exercise_arrangement ? $get_last_exercise_arrangement->arrangement + 1 : 1;
    }

    public function add_oto_exercise($name, $description, $extra_description, $date, $arrangement, $program_id)
    {
        return OneToOneProgramExercise::create([
            'name' => $name,
            'description' => $description,
            'extra_description' => $extra_description,
            'date' => $date,
            'arrangement' => $arrangement,
            'one_to_one_program_id' => $program_id,
        ]);
    }

    public function find_exercise($exercise_id)
    {
        return OneToOneProgramExercise::query()->with('videos')->find($exercise_id);
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

    public function get_other_exercises(mixed $program_id, mixed $date, mixed $exercise_id)
    {
        return OneToOneProgramExercise::query()->where(['one_to_one_program_id' => $program_id, 'date' => $date])
            ->where('id', '!=', $exercise_id)->orderBy('arrangement')->get();
    }

    public function delete_program_exercises(mixed $exercise)
    {
        return $exercise->delete();
    }

    public function delete_single_exercises(mixed $id)
    {
        return OneToOneProgramExercise::query()->where('id', $id)->delete();
    }

    public function get_workouts_done_today(string $today, mixed $coach_id)
    {
        return OneToOneProgramExercise::query()->with('one_to_one_program')->where(['date' => $today])
            ->whereHas('one_to_one_program', function ($query) use ($coach_id) {
                $query->where('coach_id', $coach_id);
            })->
            get()
            ->groupBy('one_to_one_program_id');
    }

    public function verify_client_id($client_id, $client_exercise_id)
    {
        return OneToOneProgramExercise::query()
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->where('id', $client_exercise_id)
            ->exists();
    }

    public function update_exercise_status(mixed $client_exercise_id, mixed $status)
    {
        return OneToOneProgramExercise::where('id', $client_exercise_id)->update([
            'is_done' => $status,
        ]);
    }

    public function total_today_exercises(mixed $client_id, $today)
    {
        return OneToOneProgramExercise::query()
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->where('date', $today)->count();
    }

    public function total_today_done_exercises(mixed $client_id, $today)
    {
        return OneToOneProgramExercise::query()
            ->whereHas('one_to_one_program', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            })
            ->where(['date' => $today, 'is_done' => "1"])->count();
    }

    public function get_all_program_exercises_count(mixed $program_id)
    {
        return OneToOneProgramExercise::query()->where(['one_to_one_program_id' => $program_id])->count();
    }

    public function get_all_program_done_exercises_count(mixed $program_id)
    {
        return OneToOneProgramExercise::query()->where(['one_to_one_program_id' => $program_id, 'is_done' => "1"])->count();
    }

}
