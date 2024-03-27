<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OneToOneProgramServices;
use Illuminate\Http\Request;

class OneToOneProgramController extends Controller
{
    public function __construct(protected OneToOneProgramServices $oneProgramServices)
    {
    }

    /**
     * Display a listing of programs.
     */
    public function index(Request $request)
    {
        return $this->oneProgramServices->index($request);
    }

    public function destroy(Request $request)
    {
        return $this->oneProgramServices->destroy($request);
    }
}
