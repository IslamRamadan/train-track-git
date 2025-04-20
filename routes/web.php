<?php

use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\CoachController;
use App\Http\Controllers\Dashboard\ExportUsersController;
use App\Http\Controllers\Dashboard\GymController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/verify/coach/email/{id}', [CoachController::class, "verifyCoachEmail"])->name('coach.verify.email');
Route::prefix('{locale?}')->middleware(['localized', 'AdminGuest'])->group(function () {
    Route::get('/login', [AuthController::class, "login_form"])->name('login_view');
    Route::post('/login', [AuthController::class, "login"])->name('login');
    Route::get('/welcome', function () {
        return view('mail.coach-verification-mail', ['name' => "Islam", 'user_id' => 711]);
    })->name("name");

});
Route::prefix('{locale?}')->middleware(['localized', 'AdminAuth'])->group(function () {
    Route::get('/logout', [AuthController::class, "logout"])->name('logout');
    Route::get('/coaches', [CoachController::class, "index"])->name('coaches.index');
    Route::get('/payments', [PaymentController::class, "index"])->name('payments.index');
    Route::post('/coaches/update/order/status/{id}', [PaymentController::class, "update_order_status"])->name('coach.update.order.status');
    Route::post('/coaches/block/{id}', [CoachController::class, "block"])->name('coaches.block');
    Route::post('/coaches/update/due/date/{id}', [CoachController::class, "update_due_date"])->name('coach.update.due.date');
    Route::post('/coaches/update/package/{id}', [CoachController::class, "update_package"])->name('coach.update.package');
    Route::post('/users/excel/export', [ExportUsersController::class, "exportUsersToExcel"])->name('users.excel.export');
});

Route::prefix('{locale?}')->middleware('localized')->group(function () {
    Route::get('/register/{package}', [CoachController::class, "register_form"]);
    Route::post('/register', [CoachController::class, "register"])->name('coach.register');
    Route::get('/checkout/response', [PaymentController::class, "checkout_response"])->name('checkout.response');
});
Route::get('/gym/invitation/accept', [GymController::class, "accept_gym_invitation"])->name('gym.invitation.accept');
