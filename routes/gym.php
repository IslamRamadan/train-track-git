<?php

use App\Http\Controllers\Api\GymController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:api', 'CheckSubscription', 'CheckCoachUser'])->group(function () {

    Route::group(['middleware' => 'CheckCoachNotInGym'], function () {
        Route::post('gym/add', [GymController::class, 'store']);
        Route::post('gym/send/join/request', [GymController::class, 'send_join_request']);
        Route::post('gym/list', [GymController::class, 'list']);
    });


    Route::group(['middleware' => 'CheckCoachIsOwner'], function () {
        Route::post('gym/edit', [GymController::class, 'edit']);
        Route::post('gym/delete', [GymController::class, 'delete']);
        Route::post('gym/edit/coach/privilege', [GymController::class, 'edit_coach_privilege']);
        Route::post('gym/remove/coach', [GymController::class, 'remove_coach_from_gym']);
    });
    Route::group(['middleware' => 'CheckCoachIsAdmin'], function () {
        Route::post('gym/change/join/request/status', [GymController::class, 'change_join_request_status']);
        Route::post('gym/list/join/requests', [GymController::class, 'list_join_requests']);
        Route::post('gym/coach/invite', [GymController::class, 'invite_coach_to_gym']);
        Route::post('gym/list/leave/requests', [GymController::class, 'list_leave_requests']);
        Route::post('gym/change/leave/request/status', [GymController::class, 'change_leave_request_status']);
        Route::post('gym/coach/clients/list', [GymController::class, 'list_coach_clients']);
        Route::post('client/programs/list', [GymController::class, 'index']);
        Route::post('client/program/exercises/by/date/list', [GymController::class, 'list_client_program_exercises_by_date']);

    });
    Route::group(['middleware' => 'CheckCoachIsInGym'], function () {
        Route::post('gym/list/coaches', [GymController::class, 'list_gym_coaches']);
        Route::post('gym/send/leave/request', [GymController::class, 'send_leave_request']);
        Route::post('gym/info', [GymController::class, 'info']);
    });

});

