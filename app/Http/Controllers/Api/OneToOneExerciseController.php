<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OneToOneExerciseServices;
use App\Services\OneToOneProgramServices;
use Illuminate\Http\Request;

class OneToOneExerciseController extends Controller
{
    public function __construct(protected OneToOneExerciseServices $oneToOneExerciseServices)
    {
    }

    public function list_client_exercises(Request $request)
    {
        return $this->oneToOneExerciseServices->list_client_exercises($request);
    }

    public function list_client_program_exercises_by_date(Request $request)
    {
        return $this->oneToOneExerciseServices->list_client_program_exercises_by_date($request);
    }

    public function list_client_exercises_in_date(Request $request)
    {
        return $this->oneToOneExerciseServices->list_client_exercises_in_date($request);
    }

    public function add_client_exercise(Request $request)
    {
        return $this->oneToOneExerciseServices->add_client_exercise($request);
    }

    public function copy_client_exercise(Request $request)
    {
        return $this->oneToOneExerciseServices->copy_client_exercise($request);
    }

    public function copy_client_exercise_days(Request $request)
    {
        return $this->oneToOneExerciseServices->copy_client_exercise_days($request);
    }

    public function delete_client_exercise_days(Request $request)
    {
        return $this->oneToOneExerciseServices->delete_client_exercise_days($request);
    }

    public function update_client_exercise(Request $request)
    {
        return $this->oneToOneExerciseServices->update_client_exercise($request);
    }

    public function delete_client_exercise(Request $request)
    {
        return $this->oneToOneExerciseServices->delete_client_exercise($request);
    }

    public function log_client_exercise(Request $request)
    {
        return $this->oneToOneExerciseServices->log_client_exercise($request);
    }

    public function log_client_exercise_update(Request $request)
    {
        return $this->oneToOneExerciseServices->log_client_exercise_update($request);
    }

    public function update_exercise_status(Request $request)
    {
        return $this->oneToOneExerciseServices->update_exercise_status($request);
    }

}
