<?php

use App\Http\Controllers\LessonsController;
use App\Http\Controllers\MediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CourseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Test route is working!']);
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification email resent.']);
})->middleware(['auth:sanctum', 'throttle:6,1']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/change-password', [UsersController::class, 'changePassword']);

    // Course management routes
    // Add user to course and remove user from course routes
    Route::post('/courses/{course}/add-user', [CourseController::class, 'addUserToCourse']);
    Route::post('/courses/{course}/remove-user', [CourseController::class, 'removeUserFromCourse']);
    Route::post('/courses/{course}/accept-student/{user}', [CourseController::class, 'acceptStudent']);
});

Route::middleware('auth:sanctum')->apiResource('/users', UsersController::class);
Route::middleware('auth:sanctum')->apiResource('courses', CourseController::class);
Route::middleware('auth:sanctum')->apiResource('lessons', LessonsController::class);
Route::middleware('auth:sanctum')->get('/media/{filename}', [MediaController::class, 'serve']);
