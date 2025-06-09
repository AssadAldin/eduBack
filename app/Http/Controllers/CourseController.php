<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CourseController extends Controller
{

    public function __construct()
    {
        $this->middleware('course.enrolled')->only('show');
    }
    public function index(Request $request)
{
    $user = $request->user();

    $courses = Course::with([
        'user',
        'users' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }
    ])
    ->withCount([
        'users as enrolled_students_count' => function ($q) {
            $q->where('is_accepted', true);
        },
        'users as pending_requests_count' => function ($q) {
            $q->where('is_accepted', false);
        },
        'lessons'
    ])
    ->get()
    ->map(function ($course) use ($user) {
        $enrolledUser = $course->users->first();

        $course->is_student = $enrolledUser && $enrolledUser->pivot->is_accepted;
        $course->is_requested = $enrolledUser && !$enrolledUser->pivot->is_accepted;

        unset($course->users); // optional
        return $course;
    });

    return response()->json($courses);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $course = Course::create($validated);

        return response()->json($course, 201);
    }

    public function show(Course $course)
    {
        $course->load('lessons');

        $users = $course->users()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_accepted' => $user->pivot->is_accepted,
                'joined_at' => $user->pivot->created_at,
            ];
        });

        $course->users = $users;

        return response()->json($course);
    }


    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $course->update($validated);

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function addUserToCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $course->users()->syncWithoutDetaching($validated['user_id']);

        return response()->json(['message' => 'User added to course.']);
    }

    public function removeUserFromCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $course->users()->detach($validated['user_id']);

        return response()->json(['message' => 'User removed from course.']);
    }

    public function acceptStudent(Request $request, Course $course, $userId)
    {
        // Ensure the user is already enrolled
        $isEnrolled = $course->users()->where('user_id', $userId)->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'User is not enrolled in this course'], 404);
        }

        $course->users()->updateExistingPivot($userId, ['is_accepted' => true]);

        return response()->json(['message' => 'Student accepted successfully']);
    }

}
