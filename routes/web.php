<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/', function () {
    return view('welcome');
});
