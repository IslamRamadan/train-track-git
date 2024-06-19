<?php

use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\CoachController;
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


Route::prefix('{locale?}')->middleware(['localized', 'AdminGuest'])->group(function () {
    Route::get('/login', [AuthController::class, "login_form"])->name('login_view');
    Route::post('/login', [AuthController::class, "login"])->name('login');
    Route::get('/welcome', function () {
        return view('welcome');
    })->name("name");
});
Route::prefix('{locale?}')->middleware(['localized', 'AdminAuth'])->group(function () {
    Route::get('/coaches', [CoachController::class, "index"])->name('coaches.index');
    Route::post('/coaches/block/{id}', [CoachController::class, "block"])->name('coaches.block');
    Route::post('/coaches/update/due/date/{id}', [CoachController::class, "update_due_date"])->name('coach.update.due.date');
});

Route::prefix('{locale?}')->middleware('localized')->group(function () {
    Route::get('/register/{package}', [CoachController::class, "register_form"]);
    Route::post('/register', [CoachController::class, "register"])->name('coach.register');
});
