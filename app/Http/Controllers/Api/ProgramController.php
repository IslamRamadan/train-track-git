<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProgramServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(protected ProgramServices $programServices)
    {
    }

    /**
     * Display a listing of programs.
     */
    public function index(Request $request)
    {
        return $this->programServices->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->programServices->store($request);
    }

    /**
     * List program days to use in start day and end day
     * @para¬m Request $request
     * @param Request $request
     * @return JsonResponse
     */
    public function list_program_days(Request $request): JsonResponse
    {
        return $this->programServices->list_program_days($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        return $this->programServices->update($request);
    }

    /**
     * update ongoing program sync.
     */
    public function update_sync(Request $request)
    {
        return $this->programServices->update_sync($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        return $this->programServices->destroy($request);
    }
}
