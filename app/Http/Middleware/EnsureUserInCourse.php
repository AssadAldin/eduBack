<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserInCourse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $course = $request->route('course');

        if (!$course->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Unauthorized: You are not enrolled in this course.'], 403);
        }

        return $next($request);
    }
}
