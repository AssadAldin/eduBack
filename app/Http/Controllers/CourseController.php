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

        $courses = Course::with('user', 'users')
            ->withCount('lessons')
            ->get()
            ->map(function ($course) use ($user) {
                $course->is_student = $course->users->contains($user->id);
                unset($course->users);
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
}
