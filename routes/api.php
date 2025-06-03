<?php

use App\Http\Controllers\LessonsController;
use App\Http\Controllers\MediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CourseController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Test route is working!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/users', [UsersController::class, 'index']);
Route::middleware('auth:sanctum')->apiResource('courses', CourseController::class);
Route::middleware('auth:sanctum')->apiResource('lessons', LessonsController::class);
Route::middleware('auth:sanctum')->get('/media/{filename}', [MediaController::class, 'serve']);
