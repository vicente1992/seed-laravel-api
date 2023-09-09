<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\RegisterController;

Route::post('login',  [AuthController::class, 'login'])->name('auth.login');
Route::post('register',  [RegisterController::class, 'register'])->name('auth.register');

Route::group(['middleware' => ['auth:api']], function () {
    Route::delete('logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
});
