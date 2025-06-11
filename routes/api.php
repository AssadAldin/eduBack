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

// Route to handle email verification
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// Route to resend the verification email
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
    Route::apiResource('/users', UsersController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('lessons', LessonsController::class);
    // Add, accept user to course and remove user from course routes
    Route::post('/courses/{course}/add-user', [CourseController::class, 'addUserToCourse']);
    Route::post('/courses/{course}/remove-user', [CourseController::class, 'removeUserFromCourse']);
    Route::post('/courses/{course}/accept-student/{user}', [CourseController::class, 'acceptStudent']);
    // Course progress route
    Route::get('/courses/{course}/progress', [CourseController::class, 'courseProgress']);
    // Media serving route
    Route::get('/media/{filename}', [MediaController::class, 'serve']);
});

