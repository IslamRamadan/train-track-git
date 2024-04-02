<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExerciseServices;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(protected ExerciseServices $exerciseServices)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->exerciseServices->index($request);
    }

    /**
     * Display a List program exercises by day.
     */
    public function list_program_exercises_by_day(Request $request)
    {
        return $this->exerciseServices->list_program_exercises_by_day($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return $this->exerciseServices->create($request);
    }

    /**
     * copy exercise.
     */
    public function copy(Request $request)
    {
        return $this->exerciseServices->copy($request);
    }

    /**
     * copy days of exercise .
     */
    public function copy_days(Request $request)
    {
        return $this->exerciseServices->copy_days($request);
    }

    /**
     * delete days of exercises .
     */
    public function delete_days(Request $request)
    {
        return $this->exerciseServices->delete_days($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        return $this->exerciseServices->update($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        return $this->exerciseServices->destroy($request);
    }
}
