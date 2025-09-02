<?php


// Protected routes that require authentication
use App\Http\Controllers\Api\ArticleController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
});
