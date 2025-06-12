<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonsController extends Controller
{
    public function index()
    {
        return response()->json(Lesson::with('course')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'media' => 'required|file|mimes:mp3,pdf',
            'lesson_type' => 'required|string',
            'note' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
        ]);

        $mediaPath = $request->file('media')->store('lessons');
        $validated['media'] = $mediaPath;

        $lesson = Lesson::create($validated);

        return response()->json($lesson, 201);
    }


    public function show(Lesson $lesson, Request $request)
    {
        $user = $request->user();

        $pivotData = $lesson->users()
            ->where('user_id', $user->id)
            ->first()?->pivot;

        $lesson->completed_at = $pivotData?->completed_at;
        $lesson->is_completed = $pivotData?->status === 'completed';

        return response()->json($lesson);
    }


    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'media' => 'sometimes|required|file|mimes:mp3,pdf',
            'lesson_type' => 'sometimes|required|string',
            'note' => 'nullable|string',
            'course_id' => 'sometimes|required|exists:courses,id',
        ]);

        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('lessons');
            $validated['media'] = $mediaPath;
        }

        $lesson->update($validated);

        return response()->json($lesson);
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }

    public function getProtectedPdf(Lesson $lesson)
    {
        abort_unless($lesson->media, 404);

        $path = storage_path('app/' . $lesson->media);
        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline', // Prevents download dialog
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
