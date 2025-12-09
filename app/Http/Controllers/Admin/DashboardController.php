<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Affiliate;
use App\Models\ReferralTrack;
use App\Models\Sale;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Affiliates (users dengan role affiliate)
        $totalAffiliates = User::where('role', 'affiliate')->count();
        $activeAffiliates = User::where('role', 'affiliate')->where('is_active', true)->count();

        // Total Prospek/Leads
        $totalProspects = ReferralTrack::count();
        $totalClicked = ReferralTrack::where('status', 'clicked')->count();
        $totalJoinedChannel = ReferralTrack::where('status', 'joined_channel')->count();
        $totalPurchased = ReferralTrack::where('status', 'purchased')->count();

        // Total Penjualan
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('amount');

        // Total Komisi
        $totalCommission = AffiliatePayout::sum('commission');
        $paidCommission = AffiliatePayout::where('status', 'paid')->sum('commission');
        $pendingCommission = AffiliatePayout::where('status', 'pending')->sum('commission');

        // Top Affiliates (berdasarkan jumlah prospek)
        $topAffiliates = Affiliate::withCount('referralTracks')
            ->orderBy('referral_tracks_count', 'desc')
            ->take(10)
            ->get();

        // Recent Activities (prospek terbaru)
        $recentProspects = ReferralTrack::with('affiliate.user')
            ->latest()
            ->take(10)
            ->get();

        // Statistics per bulan (6 bulan terakhir)
        $monthlyStats = ReferralTrack::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as clicked'),
            DB::raw('SUM(CASE WHEN status = "joined_channel" THEN 1 ELSE 0 END) as joined'),
            DB::raw('SUM(CASE WHEN status = "purchased" THEN 1 ELSE 0 END) as purchased')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        return view('admin.dashboard', compact(
            'totalAffiliates',
            'activeAffiliates',
            'totalProspects',
            'totalClicked',
            'totalJoinedChannel',
            'totalPurchased',
            'totalSales',
            'totalRevenue',
            'totalCommission',
            'paidCommission',
            'pendingCommission',
            'topAffiliates',
            'recentProspects',
            'monthlyStats'
        ));
    }
}
