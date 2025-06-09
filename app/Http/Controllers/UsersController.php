<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class UsersController extends Controller
{
    public function index()
    {
        return response()->json(User::all(), 200);
    }
    // Show a single user
    public function show(User $user)
    {
        return response()->json($user, 200);
    }

    // Create a new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'student'])],
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    // Update an existing user
    public function update(Request $request, User $user)
    {
        // Check if the authenticated user is admin or the owner of the account
        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            // Only admin can update the role
            'role' => [
                'sometimes',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->user()->role !== 'admin') {
                        $fail('Only admins can change the user role.');
                    }
                    if (!in_array($value, ['admin', 'student'])) {
                        $fail('Invalid role value.');
                    }
                }
            ],
        ]);

        $user->update($validated);

        return response()->json($user, 200);
    }

    // Delete a user
    public function destroy(Request $request, User $user)
    {
        $authUser = $request->user();

        // Allow if admin or deleting own account
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json(['message' => 'Forbidden. You can only delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.'], 200);
    }

}
