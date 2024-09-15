<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\CoachService;
use Illuminate\Http\Request;

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

    public function register_form($lang, $package)
    {
        return $this->coachService->register_form($package);
    }

    public function register($lang, Request $request)
    {
        return $this->coachService->register($request);
    }

    public function update_due_date($lang, $id, Request $request)
    {
        return $this->coachService->update_coach_due_date($id, $request);
    }

    public function update_package($lang, $id, Request $request)
    {
        return $this->coachService->update_coach_package($id, $request);
    }
}
