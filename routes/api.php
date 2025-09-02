<?php
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
});

Route::post('/login', [UserController::class, 'login']);
