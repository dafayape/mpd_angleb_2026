<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route yang butuh autentikasi
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
