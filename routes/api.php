<?php
use App\Http\Controllers\Api\ArticleController;

Route::apiResource('articles', ArticleController::class);