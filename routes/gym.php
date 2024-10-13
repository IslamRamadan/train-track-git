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
    Route::post('gym/add', [GymController::class, 'store']);

    Route::post('gym/edit', [GymController::class, 'edit']);//not yet
    Route::post('gym/list', [GymController::class, 'list']);//not yet
    Route::post('gym/delete', [GymController::class, 'delete']);//not yet

    Route::post('gym/coach/invite', [GymController::class, 'invite_coach_to_gym']);

    Route::post('gym/list/coaches', [GymController::class, 'list_gym_coaches']);
    Route::post('gym/list/admins', [GymController::class, 'list_gym_admins']);
    Route::post('gym/list/join/requests', [GymController::class, 'list_join_requests']);
    Route::post('gym/list/leave/requests', [GymController::class, 'list_leave_requests']);
    Route::post('gym/change/join/request/status', [GymController::class, 'change_join_request_status']);
    Route::post('gym/change/leave/request/status', [GymController::class, 'change_leave_request_status']);
    Route::post('gym/edit/coach/privilege', [GymController::class, 'edit_coach_privilege']);
    Route::post('gym/send/join/request', [GymController::class, 'send_join_request']);


});
