<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DownloadLog::with(['user:id,username,family_name,given_name,role', 'research:id,title']);

        if ($request->filled('research_id')) {
            $query->where('research_id', $request->research_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('role')) {
            $query->where('role_at_time', $request->role);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('downloaded_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('downloaded_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('research', function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%");
            })->orWhereHas('user', function ($qry) use ($q) {
                $qry->where('username', 'like', "%{$q}%")
                    ->orWhere('family_name', 'like', "%{$q}%")
                    ->orWhere('given_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->orderBy('downloaded_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($logs);
    }

    public function stats(Request $request): JsonResponse
    {
        $totalPerResearch = DownloadLog::selectRaw('research_id, COUNT(*) as total')
            ->groupBy('research_id')
            ->with('research:id,title')
            ->get();

        $totalPerUser = DownloadLog::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->with('user:id,username,family_name,given_name')
            ->get();

        $mostDownloaded = DownloadLog::selectRaw('research_id, COUNT(*) as count')
            ->groupBy('research_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('research:id,title')
            ->get();

        return response()->json([
            'total_per_research' => $totalPerResearch,
            'total_per_user' => $totalPerUser,
            'most_downloaded' => $mostDownloaded,
        ]);
    }
}
