<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GymServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function __construct(protected GymServices $gymServices)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->gymServices->store($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        return $this->gymServices->destroy($request);
    }

    /**
     * Invite coach to gym
     */
    public function invite_coach_to_gym(Request $request)
    {
        return $this->gymServices->invite_coach_to_gym($request);
    }

    /**
     * list gym coaches
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_coaches(Request $request)
    {
        return $this->gymServices->list_gym_coaches($request);
    }

    /**
     * list gym join requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_join_requests(Request $request)
    {
        return $this->gymServices->list_join_requests($request);
    }


    /**
     * change join request status
     *
     * @param Request $request
     * @return mixed
     */
    public function change_join_request_status(Request $request)
    {
        return $this->gymServices->change_join_request_status($request);
    }

    /**
     * send leave request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send_leave_request(Request $request)
    {
        return $this->gymServices->send_leave_request($request);
    }

    /**
     * list leave requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_leave_requests(Request $request)
    {
        return $this->gymServices->list_leave_requests($request);
    }
    /**
     * change leave request status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function change_leave_request_status(Request $request)
    {
        return $this->gymServices->change_leave_request_status($request);
    }

    /**
     * Edit coach privilege
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit_coach_privilege(Request $request)
    {
        return $this->gymServices->edit_coach_privilege($request);
    }

    /**
     * Remove coach from gym
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_coach_from_gym(Request $request)
    {
        return $this->gymServices->remove_coach_from_gym($request);
    }

    /**
     * Send join request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send_join_request(Request $request)
    {
        return $this->gymServices->send_join_request($request);
    }

    /**
     * List Gyms
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return $this->gymServices->list($request);
    }

    /**
     * Edit Gym info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request)
    {
        return $this->gymServices->edit($request);
    }

    /**
     * Get Gym info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request)
    {
        return $this->gymServices->info($request);
    }

    /**
     * List coach clients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_coach_clients(Request $request)
    {
        return $this->gymServices->list_coach_clients($request);
    }

    /**
     * list program exercises by day
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_client_program_exercises_by_date(Request $request)
    {
        return $this->gymServices->list_client_program_exercises_by_date($request);
    }

    /**
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function list_client_programs(Request $request)
    {
        return $this->gymServices->list_client_programs($request);
    }


    public function list_programs_exercises(Request $request)
    {
        return $this->gymServices->list_programs_exercises($request);
    }

    /**
     * Add client exercise (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add_client_exercise(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['client_program_id'];

        $validationResult = $this->gymServices->validateClientProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->add_client_exercise($request);
    }

    /**
     * Edit client exercise (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit_client_exercise(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['client_exercise_id'];

        $validationResult = $this->gymServices->validateClientExercise($exercise_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->update_client_exercise($request);
    }

    /**
     * Delete client exercise (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_client_exercise(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['client_exercise_id'];

        $validationResult = $this->gymServices->validateClientExercise($exercise_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->delete_client_exercise($request);
    }

    /**
     * Copy client exercise (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function copy_client_exercise(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $exercise_id = $request['client_exercise_id'];
        $to_program_id = $request['to_client_program_id'];

        // Validate both the source exercise and destination program
        $validationResult = $this->gymServices->validateClientExercise($exercise_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->gymServices->validateClientProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->copy_client_exercise($request);
    }

    /**
     * Copy client exercise days (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function copy_client_exercise_days(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $from_program_id = $request['from_client_program_id'];
        $to_program_id = $request['to_client_program_id'];

        // Validate both programs
        $validationResult = $this->gymServices->validateClientProgram($from_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->gymServices->validateClientProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->copy_client_exercise_days($request);
    }

    /**
     * Cut client exercise days (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cut_client_exercise_days(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $from_program_id = $request['from_client_program_id'];
        $to_program_id = $request['to_client_program_id'];

        // Validate both programs
        $validationResult = $this->gymServices->validateClientProgram($from_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $validationResult = $this->gymServices->validateClientProgram($to_program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->cut_client_exercise_days($request);
    }

    /**
     * Delete client exercise days (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_client_exercise_days(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['client_program_id'];

        $validationResult = $this->gymServices->validateClientProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->delete_client_exercise_days($request);
    }

    /**
     * Delete client program (gym admin/owner only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_client_program(Request $request)
    {
        $admin_gym_id = $request->user()->gym_coach->gym_id;
        $program_id = $request['client_program_id'];

        $validationResult = $this->gymServices->validateClientProgram($program_id, $admin_gym_id);
        if ($validationResult !== true) {
            return $validationResult;
        }

        return $this->gymServices->delete_client_program($request);
    }

    /**
     * List gym programs (similar to ProgramController::index())
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_programs(Request $request)
    {
        return $this->gymServices->list_gym_programs($request);
    }

    /**
     * Add gym program
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add_gym_program(Request $request)
    {
        return $this->gymServices->add_gym_program($request);
    }

    /**
     * Update gym program
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update_gym_program(Request $request)
    {
        return $this->gymServices->update_gym_program($request);
    }

    /**
     * Delete gym program
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_gym_program(Request $request)
    {
        return $this->gymServices->delete_gym_program($request);
    }

    /**
     * Update gym program sync
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update_gym_program_sync(Request $request)
    {
        return $this->gymServices->update_gym_program_sync($request);
    }

    /**
     * List gym program days
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_program_days(Request $request)
    {
        return $this->gymServices->list_gym_program_days($request);
    }

    /**
     * List gym program exercises
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_program_exercises(Request $request)
    {
        return $this->gymServices->list_gym_program_exercises($request);
    }

    /**
     * List gym program exercises by day
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list_gym_program_exercises_by_day(Request $request)
    {
        return $this->gymServices->list_gym_program_exercises_by_day($request);
    }

    /**
     * Add gym program exercise
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add_gym_program_exercise(Request $request)
    {
        return $this->gymServices->add_gym_program_exercise($request);
    }

    /**
     * Update gym program exercise
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update_gym_program_exercise(Request $request)
    {
        return $this->gymServices->update_gym_program_exercise($request);
    }

    /**
     * Delete gym program exercise
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_gym_program_exercise(Request $request)
    {
        return $this->gymServices->delete_gym_program_exercise($request);
    }

    /**
     * Copy gym program exercise
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function copy_gym_program_exercise(Request $request)
    {
        return $this->gymServices->copy_gym_program_exercise($request);
    }

    /**
     * Copy gym program exercise days
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function copy_gym_program_exercise_days(Request $request)
    {
        return $this->gymServices->copy_gym_program_exercise_days($request);
    }

    /**
     * Cut gym program exercise days
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cut_gym_program_exercise_days(Request $request)
    {
        return $this->gymServices->cut_gym_program_exercise_days($request);
    }

    /**
     * Delete gym program exercise days
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete_gym_program_exercise_days(Request $request)
    {
        return $this->gymServices->delete_gym_program_exercise_days($request);
    }

}
