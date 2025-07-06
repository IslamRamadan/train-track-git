<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VVExerciseServices;
use Illuminate\Http\Request;

class VVExerciseController extends Controller
{
    public function __construct(protected VVExerciseServices $exerciseServices)
    {
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
     * cut days of exercise .
     */
    public function cut_days(Request $request)
    {
        return $this->exerciseServices->cut_days($request);
    }
}
