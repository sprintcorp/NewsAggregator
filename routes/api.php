<?php

use App\Http\Controllers\api\v1\PreferenceController;
use App\Http\Controllers\api\v1\ArticleController;
use App\Http\Controllers\api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('resetPassword', [AuthController::class, 'resetPassword']);
Route::post('logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{id}', [ArticleController::class, 'show']);
    Route::post('preferences', [PreferenceController::class, 'store']);
    Route::get('preferences', [PreferenceController::class, 'show']);
    Route::get('prefered/article', [PreferenceController::class, 'index']);
});
