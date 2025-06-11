<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function courseProgress(Course $course, Request $request)
    {
        $user = $request->user();

        $totalLessons = $course->lessons()->count();

        $completedLessons = $course->lessons()
            ->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'completed');
            })->count();

        $progress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100, 2)
            : 0;

        return response()->json([
            'course_id' => $course->id,
            'progress' => $progress,
            'completed' => $completedLessons,
            'total' => $totalLessons,
        ]);
    }

    public function complete(Lesson $lesson, Request $request)
    {
        $user = $request->user();

        // Attach or update the lesson_user pivot with 'completed' status
        $user->completedLessons()->syncWithoutDetaching([
            $lesson->id => ['status' => 'completed', 'completed_at' => now()]
        ]);

        return response()->json([
            'message' => 'Lesson marked as completed.',
            'lesson_id' => $lesson->id,
            'status' => 'completed'
        ]);
    }
}
