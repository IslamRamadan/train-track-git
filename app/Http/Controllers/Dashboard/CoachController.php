<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\CoachService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    public function __construct(protected CoachService $coachService)
    {
    }

    public function index(Request $request)
    {
        return $this->coachService->index($request);
    }

    public function block($lang, $id, Request $request)
    {
        return $this->coachService->block($id, $request);
    }
}
