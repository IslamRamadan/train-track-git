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

});
