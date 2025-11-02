<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\VolunteerImportController;

// default root to login
Route::get('/', function () {
    return redirect()->route('auth.login');
});

// login
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');

// registration
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');

// homepage
Route::get('/home', [HomePageController::class, 'index'])
    ->name('home'); // no middleware for now, just for testing

// logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// volunteer imports
Route::middleware('auth:admin')->group(function () {
    Route::get('/volunteer-import', [VolunteerImportController::class, 'index'])->name('volunteer.import.index');
    Route::post('/volunteer-import', [VolunteerImportController::class, 'store'])->name('volunteer.import.store');
    Route::post('/volunteer-import/{import}/validate', [VolunteerImportController::class, 'validateEntries'])->name('volunteer.import.validate');
    Route::post('/volunteer-import/{import}/submit', [VolunteerImportController::class, 'submitToDatabase'])->name('volunteer.import.submit');
});