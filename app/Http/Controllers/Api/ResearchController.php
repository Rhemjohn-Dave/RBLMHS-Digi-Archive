<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\DownloadPermission;
use App\Models\Research;
use App\Models\User;
use App\Notifications\NewResearchUploaded;
use App\Notifications\ResearchStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Research::where('status', 'approved')->with('faculty:id,family_name,given_name,username');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('authors', 'like', "%{$q}%")
                    ->orWhere('abstract', 'like', "%{$q}%");
            });
        }
        if ($request->filled('year')) {
            $query->whereYear('published_date', $request->year);
        }

        $research = $query->orderBy('published_date', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($research);
    }

    public function show(Research $research): JsonResponse
    {
        if (!$research->isApproved() && $research->faculty_id !== request()->user()->id && !request()->user()->isAdmin()) {
            abort(404);
        }
        $research->load('faculty:id,family_name,given_name,username');
        $user = request()->user();
        if ($user->isStudent()) {
            $perm = DownloadPermission::where('user_id', $user->id)->where('research_id', $research->id)->first();
            $research->download_permission = $perm ? $perm->status === 'approved' : false;
        } else {
            $research->download_permission = true;
        }
        return response()->json($research);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'authors' => 'required|string|max:500',
            'co_authors' => 'nullable|string|max:500',
            'adviser' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'abstract' => 'nullable|string',
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB
        ]);

        $file = $request->file('file');
        $path = $file->store('research', 'public');
        $validated['file_path'] = $path;
        $validated['faculty_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $research = Research::create($validated);
        $research->load('faculty');
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewResearchUploaded($research));
        return response()->json($research, 201);
    }

    public function download(Request $request, Research $research): JsonResponse|StreamedResponse
    {
        if (!$research->isApproved()) {
            return response()->json(['message' => 'Research is not available for download.'], 403);
        }

        $user = $request->user();

        if ($user->isStudent()) {
            $permission = DownloadPermission::where('user_id', $user->id)
                ->where('research_id', $research->id)
                ->where('status', 'approved')
                ->first();
            if (!$permission) {
                return response()->json(['message' => 'You do not have download permission for this research.'], 403);
            }
        }
        // Faculty can download without permission

        $path = $research->file_path;
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        DownloadLog::create([
            'user_id' => $user->id,
            'research_id' => $research->id,
            'ip_address' => $request->ip(),
            'role_at_time' => $user->role,
        ]);
        $research->increment('download_count');

        return Storage::disk('public')->download(
            $path,
            str_replace(['/', '\\'], '-', $research->title) . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function mySubmissions(Request $request): JsonResponse
    {
        $research = Research::where('faculty_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($research);
    }

    public function pending(Request $request): JsonResponse
    {
        $research = Research::where('status', 'pending')
            ->with('faculty:id,family_name,given_name,username')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($research);
    }

    public function approve(Research $research): JsonResponse
    {
        if ($research->status !== 'pending') {
            return response()->json(['message' => 'Research is not pending.'], 422);
        }
        $research->update(['status' => 'approved']);
        $research->faculty?->notify(new ResearchStatusChanged($research, 'approved'));
        return response()->json($research->fresh()->load('faculty'));
    }

    public function reject(Research $research): JsonResponse
    {
        if ($research->status !== 'pending') {
            return response()->json(['message' => 'Research is not pending.'], 422);
        }
        $research->update(['status' => 'rejected']);
        $research->faculty?->notify(new ResearchStatusChanged($research, 'rejected'));
        return response()->json($research->fresh()->load('faculty'));
    }

    /** Admin: list published (approved) research. */
    public function published(Request $request): JsonResponse
    {
        $query = Research::where('status', 'approved')
            ->with('faculty:id,family_name,given_name,username')
            ->orderBy('published_date', 'desc');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('authors', 'like', "%{$q}%")
                    ->orWhere('abstract', 'like', "%{$q}%");
            });
        }

        $research = $query->paginate($request->get('per_page', 15));
        return response()->json($research);
    }

    /** Admin: update research (published or any). */
    public function update(Request $request, Research $research): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:500',
            'authors' => 'sometimes|string|max:500',
            'co_authors' => 'nullable|string|max:500',
            'adviser' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'abstract' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        if (isset($validated['file'])) {
            $file = $request->file('file');
            $path = $file->store('research', 'public');
            if ($research->file_path && Storage::disk('public')->exists($research->file_path)) {
                Storage::disk('public')->delete($research->file_path);
            }
            $validated['file_path'] = $path;
            unset($validated['file']);
        }

        unset($validated['file']);
        $research->update($validated);
        return response()->json($research->fresh()->load('faculty'));
    }
}
