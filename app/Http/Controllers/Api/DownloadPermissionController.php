<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadPermission;
use App\Models\Research;
use App\Models\User;
use App\Notifications\DownloadRequestByStudent;
use App\Notifications\DownloadRequestProcessed;
use App\Notifications\NewDownloadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DownloadPermissionController extends Controller
{
    public function requestDownload(Request $request, Research $research): JsonResponse
    {
        $user = $request->user();
        if (!$user->isStudent()) {
            return response()->json(['message' => 'Only students need to request download permission.'], 422);
        }
        if (!$research->isApproved()) {
            return response()->json(['message' => 'Research is not approved.'], 422);
        }

        $existing = DownloadPermission::where('user_id', $user->id)->where('research_id', $research->id)->first();
        if ($existing) {
            return response()->json([
                'message' => $existing->status === 'pending'
                    ? 'Request already pending.'
                    : 'You already have ' . $existing->status . ' permission.',
            ], 422);
        }

        $permission = DownloadPermission::create([
            'user_id' => $user->id,
            'research_id' => $research->id,
            'status' => 'pending',
        ]);
        $permission->load(['user', 'research']);
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewDownloadRequest($permission));
        $research->faculty?->notify(new DownloadRequestByStudent($permission));
        return response()->json($permission, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = DownloadPermission::with(['user:id,username,family_name,given_name,role', 'research:id,title']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $permissions = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($permissions);
    }

    public function approve(DownloadPermission $permission): JsonResponse
    {
        if ($permission->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }
        $permission->update(['status' => 'approved']);
        $permission->user?->notify(new DownloadRequestProcessed($permission, 'approved'));
        return response()->json($permission->fresh()->load(['user', 'research']));
    }

    public function reject(DownloadPermission $permission): JsonResponse
    {
        if ($permission->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }
        $permission->update(['status' => 'rejected']);
        $permission->user?->notify(new DownloadRequestProcessed($permission, 'rejected'));
        return response()->json($permission->fresh()->load(['user', 'research']));
    }
}
