<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('username', 'like', "%{$q}%")
                    ->orWhere('family_name', 'like', "%{$q}%")
                    ->orWhere('given_name', 'like', "%{$q}%")
                    ->orWhere('lrn', 'like', "%{$q}%")
                    ->orWhere('faculty_number', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->makeHidden(['password']));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $rules = [
            'family_name' => 'sometimes|string|max:255',
            'given_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
        ];
        if ($user->role === 'student') {
            $rules['lrn'] = 'sometimes|string|max:20|unique:users,lrn,' . $user->id;
        }
        if ($user->role === 'faculty') {
            $rules['faculty_number'] = 'sometimes|string|max:50|unique:users,faculty_number,' . $user->id;
        }
        $rules['username'] = 'sometimes|string|max:255|unique:users,username,' . $user->id;

        $validated = $request->validate($rules);
        $user->update($validated);
        return response()->json($user->fresh()->makeHidden(['password']));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot delete admin user.'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    public function approve(User $user): JsonResponse
    {
        if ($user->status !== 'pending') {
            return response()->json(['message' => 'User is not pending.'], 422);
        }
        $user->update(['status' => 'approved']);
        $user->notify(new UserStatusChanged('approved'));
        return response()->json($user->fresh()->makeHidden(['password']));
    }

    public function reject(User $user): JsonResponse
    {
        if ($user->status !== 'pending') {
            return response()->json(['message' => 'User is not pending.'], 422);
        }
        $user->update(['status' => 'rejected']);
        $user->notify(new UserStatusChanged('rejected'));
        return response()->json($user->fresh()->makeHidden(['password']));
    }
}
