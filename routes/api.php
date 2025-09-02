<?php
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/login', [UserController::class, 'login']);

// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
});
