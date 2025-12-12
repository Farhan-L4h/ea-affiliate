<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Affiliate;
use App\Models\ReferralTrack;
use App\Models\Sale;
use App\Models\Order;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Affiliates
        $totalAffiliates = Affiliate::count();
        $activeAffiliates = Affiliate::count();

        // Total Prospek/Leads
        $totalProspects = ReferralTrack::count();
        $totalJoinedChannel = ReferralTrack::where('status', 'joined_channel')->count();
        $totalPurchased = ReferralTrack::where('status', 'purchased')->count();

        // Total Orders
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $paidOrders = Order::where('status', 'paid')->count();
        $expiredOrders = Order::where('status', 'expired')->count();

        // Total Penjualan
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('sale_amount');

        // Orders Revenue
        $ordersRevenue = Order::where('status', 'paid')->sum('base_amount');

        // Total Komisi
        $totalCommission = AffiliatePayout::sum('commission');
        $paidCommission = AffiliatePayout::where('status', 'paid')->sum('commission');
        $pendingCommission = AffiliatePayout::where('status', 'pending')->sum('commission');

        // Top Affiliates (berdasarkan jumlah referral)
        $topAffiliates = Affiliate::with('user')
            ->withCount('referralTracks')
            ->orderBy('referral_tracks_count', 'desc')
            ->take(10)
            ->get();

        // Recent Orders
        $recentOrders = Order::with(['affiliate'])
            ->latest()
            ->take(10)
            ->get();

        // Recent Sales
        $recentSales = Sale::with(['affiliate.user', 'order'])
            ->latest('sale_date')
            ->take(10)
            ->get();

        // Monthly revenue (6 months)
        $monthlyRevenue = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_key'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN status = "paid" THEN base_amount ELSE 0 END) as revenue')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month_key', 'month')
        ->orderBy('month_key', 'asc')
        ->get();

        return view('admin.dashboard', compact(
            'totalAffiliates',
            'activeAffiliates',
            'totalProspects',
            'totalJoinedChannel',
            'totalPurchased',
            'totalOrders',
            'pendingOrders',
            'paidOrders',
            'expiredOrders',
            'totalSales',
            'totalRevenue',
            'ordersRevenue',
            'totalCommission',
            'paidCommission',
            'pendingCommission',
            'topAffiliates',
            'recentOrders',
            'recentSales',
            'monthlyRevenue'
        ));
    }
}
