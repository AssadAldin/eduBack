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

        // Check if user is enrolled in the course
        $course = $lesson->course;
        $isEnrolled = $course->users()
            ->where('user_id', $user->id)
            ->wherePivot('is_accepted', true)
            ->exists();

        if (!$isEnrolled) {
            return response()->json([
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Check if the user already has a pivot record
        $existing = $user->completedLessons()
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existing) {
            // Update the pivot record
            $user->completedLessons()->updateExistingPivot($lesson->id, [
                'status' => 'completed',
                'completed_at' => now()
            ]);
        } else {
            // Create a new pivot record
            $user->completedLessons()->attach($lesson->id, [
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }

        return response()->json([
            'message' => 'Lesson marked as completed.',
            'lesson_id' => $lesson->id,
            'status' => 'completed'
        ]);
    }


    public function removeCompletion(Lesson $lesson, Request $request)
    {
        $user = $request->user();

        // Detach the lesson from the user's completed lessons
        $user->completedLessons()->detach($lesson->id);

        return response()->json([
            'message' => 'Lesson marked as not completed.',
            'lesson_id' => $lesson->id,
            'status' => 'in_progress'
        ]);
    }

}
