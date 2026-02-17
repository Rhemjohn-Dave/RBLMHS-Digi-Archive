<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\Research;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $totalResearch = Research::count();
        $pendingResearch = Research::where('status', 'pending')->count();
        $totalDownloads = DownloadLog::count();

        $downloadsPerMonth = DownloadLog::query()
            ->selectRaw('YEAR(downloaded_at) as year, MONTH(downloaded_at) as month, COUNT(*) as count')
            ->whereNotNull('downloaded_at')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $mostDownloaded = Research::where('status', 'approved')
            ->orderByDesc('download_count')
            ->limit(10)
            ->get(['id', 'title', 'download_count']);

        return response()->json([
            'total_users' => $totalUsers,
            'pending_users' => $pendingUsers,
            'total_research' => $totalResearch,
            'pending_research' => $pendingResearch,
            'total_downloads' => $totalDownloads,
            'downloads_per_month' => $downloadsPerMonth,
            'most_downloaded' => $mostDownloaded,
        ]);
    }
}
