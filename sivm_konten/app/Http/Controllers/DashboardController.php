<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KontenMarketing;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get content statistics for dashboard
     */
    public function getContentStats()
    {
        $stats = [
            'planning' => KontenMarketing::where('status', 'Draft')->count(),
            'creation' => KontenMarketing::where('status', 'Draft')->count(),
            'approval' => KontenMarketing::where('status', 'Scheduled')->count(),
            'scheduled' => KontenMarketing::where('status', 'Posted')->count(),
            'total' => KontenMarketing::count(),
        ];

        // Platform breakdown
        $platformStats = KontenMarketing::select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get()
            ->pluck('total', 'platform');

        // Monthly trend
        $monthlyTrend = KontenMarketing::select(
                DB::raw('MONTH(tanggal_posting) as month'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal_posting', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        return response()->json([
            'stats' => $stats,
            'platformStats' => $platformStats,
            'monthlyTrend' => $monthlyTrend
        ]);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics()
    {
        // Overall statistics
        $overallStats = [
            'totalEngagementRate' => KontenMarketing::avg('engagement_rate') ?? 0,
            'totalViews' => KontenMarketing::sum('views') ?? 0,
            'avgEngagement' => KontenMarketing::avg('engagement_rate') ?? 0,
        ];

        // Top performing content
        $topPerforming = KontenMarketing::with('creatorUser')
            ->orderBy('engagement_rate', 'desc')
            ->take(5)
            ->get();

        // Worst performing content
        $worstPerforming = KontenMarketing::with('creatorUser')
            ->where('engagement_rate', '<', 5)
            ->orderBy('engagement_rate', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'overallStats' => $overallStats,
            'topPerforming' => $topPerforming,
            'worstPerforming' => $worstPerforming
        ]);
    }

    /**
     * Get users data
     */
    public function getUsers()
    {
        $users = User::with('roles')
            ->withCount(['kontenMarketing as content_count'])
            ->get();

        return response()->json($users);
    }
}
