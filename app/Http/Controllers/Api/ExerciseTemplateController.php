<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExerciseTemplateServices;
use Illuminate\Http\Request;

class ExerciseTemplateController extends Controller
{
    public function __construct(protected ExerciseTemplateServices $exerciseTemplateServices)
    {

    }

    public function add(Request $request)
    {
        return $this->exerciseTemplateServices->add($request);
    }

    public function edit(Request $request)
    {
        return $this->exerciseTemplateServices->edit($request);
    }

    public function list(Request $request)
    {
        return $this->exerciseTemplateServices->list($request);
    }

    public function delete(Request $request)
    {
        return $this->exerciseTemplateServices->delete($request);
    }


}
