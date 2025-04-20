<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CoachVideosServices;
use Illuminate\Http\Request;

class CoachVideosController extends Controller
{
    public function __construct(protected CoachVideosServices $coachVideosServices)
    {

    }

    public function add(Request $request)
    {
        return $this->coachVideosServices->add($request);
    }

    public function edit(Request $request)
    {
        return $this->coachVideosServices->edit($request);
    }

    public function list(Request $request)
    {
        return $this->coachVideosServices->list($request);
    }

    public function delete(Request $request)
    {
        return $this->coachVideosServices->delete($request);
    }

 public function import(Request $request)
    {
        return $this->coachVideosServices->import($request);
    }


}
