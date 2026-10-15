<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::prefix('v1')->group(function () {

    // Autenticación (públicas)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Lectura (públicas)
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']);

    // Requieren el header Authorization: Bearer <token>
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/posts', [PostController::class, 'store']);
        Route::delete('/posts/{id}', [PostController::class, 'destroy']);
        Route::post('/posts/{id}/like', [PostController::class, 'like']);
        Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    });
});