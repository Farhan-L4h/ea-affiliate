<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class AffiliateDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil affiliate user ini (atau bikin kalau belum ada)
        $affiliate = Affiliate::firstOrCreate(
            ['user_id' => $user->id],
            ['ref_code' => $this->generateAffiliateCode()]
        );

        $totalCommission = AffiliatePayout::where('affiliate_ref', $affiliate->ref_code)
            ->sum('commission');

        $totalSales = AffiliatePayout::where('affiliate_ref', $affiliate->ref_code)
            ->count();

        $totalClicks = $affiliate->total_clicks;
        $totalJoins  = $affiliate->total_joins;

        $recentSales = Sale::where('affiliate_ref', $affiliate->ref_code)
            ->latest()
            ->take(10)
            ->get();

        // 🟢 Kirim semua ke view "dashboard"
        return view('dashboard', compact(
            'affiliate',
            'totalCommission',
            'totalSales',
            'totalClicks',
            'totalJoins',
            'recentSales'
        ));
    }

    protected function generateAffiliateCode(): string
    {
        do {
            $code = 'AF' . strtoupper(str()->random(6));
        } while (Affiliate::where('ref_code', $code)->exists());

        return $code;
    }
}
