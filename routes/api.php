<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\CoachVideosController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OneToOneExerciseController;
use App\Http\Controllers\Api\OneToOneProgramController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('client/register', [AuthController::class, 'client_register']);
Route::post('coach/register', [AuthController::class, 'coach_register']);
Route::post('forget/password', [AuthController::class, 'forget_password']);
Route::post('checkout/processed', [PaymentController::class, 'checkout_processed']);
Route::post('coach/payment/link/create', [CoachController::class, 'create_payment_link']);
Route::post('coach/get/package', [CoachController::class, 'get_package']);

Route::middleware(['auth:api', 'CheckSubscription'])->group(function () {
    // Coach apis start
    Route::post('programs/list', [ProgramController::class, 'index']);
    Route::post('program/add', [ProgramController::class, 'store']);
    Route::post('program/edit', [ProgramController::class, 'update']);
    Route::post('program/update/sync', [ProgramController::class, 'update_sync']);
    Route::post('program/delete', [ProgramController::class, 'destroy']);
    Route::post('program/exercises/list', [ExerciseController::class, 'index']);
    Route::post('program/exercises/by/day/list', [ExerciseController::class, 'list_program_exercises_by_day']);
    Route::post('program/days/list', [ProgramController::class, 'list_program_days']);
    Route::post('program/exercise/add', [ExerciseController::class, 'create']);
    Route::post('program/exercise/copy', [ExerciseController::class, 'copy']);
    Route::post('program/exercise/days/copy', [ExerciseController::class, 'copy_days']);
    Route::post('program/exercise/days/cut', [ExerciseController::class, 'cut_days']);//6
    Route::post('program/exercise/days/delete', [ExerciseController::class, 'delete_days']);//7
    Route::post('program/exercise/edit', [ExerciseController::class, 'update']);
    Route::post('program/exercise/delete', [ExerciseController::class, 'destroy']);//5
    Route::post('program/client/assign', [ClientController::class, 'assign_program_to_client']);
    Route::post('clients/list', [ClientController::class, 'index']);
    Route::post('coach/client/assign', [ClientController::class, 'assign_client_to_coach']);
    Route::post('coach/client/invitation/delete', [ClientController::class, 'remove_client_invitation']);
    Route::post('coach/client/archive', [ClientController::class, 'coach_archive_client']);
    Route::post('client/programs/list', [OneToOneProgramController::class, 'index']);
    Route::post('client/programs/delete', [OneToOneProgramController::class, 'destroy']);//2
    Route::post('client/program/exercises/list', [OneToOneExerciseController::class, 'list_client_exercises']);
    Route::post('client/program/exercises/by/date/list', [OneToOneExerciseController::class, 'list_client_program_exercises_by_date']);
    Route::post('client/program/exercise/add', [OneToOneExerciseController::class, 'add_client_exercise']);
    Route::post('client/program/exercise/copy', [OneToOneExerciseController::class, 'copy_client_exercise']);
    Route::post('client/program/exercise/days/copy', [OneToOneExerciseController::class, 'copy_client_exercise_days']);
    Route::post('client/program/exercise/days/cut', [OneToOneExerciseController::class, 'cut_client_exercise_days']);//3
    Route::post('client/program/exercise/days/delete', [OneToOneExerciseController::class, 'delete_client_exercise_days']);//4
    Route::post('client/program/exercise/edit', [OneToOneExerciseController::class, 'update_client_exercise']);
    Route::post('client/program/exercise/delete', [OneToOneExerciseController::class, 'delete_client_exercise']);//1
    Route::post('coach/dashboard', [CoachController::class, 'coach_dashboard']);
    Route::post('clients/activities', [CoachController::class, 'clients_activities']);
    Route::post('client/update/due/date', [CoachController::class, 'update_due_date']);
    Route::post('coach/update/profile', [CoachController::class, 'update_info']);
    Route::post('coach/client/logs', [CoachController::class, 'list_client_logs']);
    Route::post('check/package/limit', [CoachController::class, 'check_package_limit']);

    Route::post('coach/videos/add', [CoachVideosController::class, 'add']);
    Route::post('coach/videos/edit', [CoachVideosController::class, 'edit']);
    Route::post('coach/videos/list', [CoachVideosController::class, 'list']);
    Route::post('coach/videos/delete', [CoachVideosController::class, 'delete']);

    // Coach apis end

    // Client apis start
    Route::post('client/date/exercises/list', [OneToOneExerciseController::class, 'list_client_exercises_in_date']);
    Route::post('client/exercise/log', [OneToOneExerciseController::class, 'log_client_exercise']);
    Route::post('client/exercise/log/update', [OneToOneExerciseController::class, 'log_client_exercise_update']);
    Route::post('client/update/exercises/status', [OneToOneExerciseController::class, 'update_exercise_status']);
    Route::post('client/profile/info', [ClientController::class, 'profile_info']);
    Route::post('client/update/info', [ClientController::class, 'update_info']);
    Route::post('client/logs/list', [LogController::class, 'client_logs_list']);
    Route::post('client/program/logs/list', [LogController::class, 'client_programs_logs_list']);
    Route::post('client/dashboard', [ClientController::class, 'client_dashboard']);
    Route::post('client/archive/account', [ClientController::class, 'archive_account']);
    Route::post('client/delete', [ClientController::class, 'delete_client']);
    Route::post('client/delete/account', [ClientController::class, 'delete']);
    Route::post('change/password', [AuthController::class, 'change_password']);
    // Client apis end

    // Mutual apis start
    Route::post('notification/list', [NotificationController::class, 'list_notifications']);
    Route::post('send/coaches/notification', [NotificationController::class, 'send_coaches_notification']);
    Route::post('comment/add', [CommentController::class, 'add']);
    Route::post('comment/delete', [CommentController::class, 'delete']);
    Route::post('version/update', [AuthController::class, 'update_version']);
    // Mutual apis end


});
