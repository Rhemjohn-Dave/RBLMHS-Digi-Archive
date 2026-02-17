<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user->loadMissing([])->makeHidden(['password']),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:student,faculty',
        ]);

        if ($request->role === 'student') {
            $validated = $request->validate([
                'lrn' => 'required|string|max:20|unique:users,lrn',
                'family_name' => 'required|string|max:255',
                'given_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $validated['role'] = 'student';
            $validated['status'] = 'pending';
        } else {
            $validated = $request->validate([
                'faculty_number' => 'required|string|max:50|unique:users,faculty_number',
                'family_name' => 'required|string|max:255',
                'given_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $validated['role'] = 'faculty';
            $validated['status'] = 'approved';
        }

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        if ($user->status === 'pending') {
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new NewRegistrationRequest($user));
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => $user->makeHidden(['password']),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->makeHidden(['password']));
    }
}
