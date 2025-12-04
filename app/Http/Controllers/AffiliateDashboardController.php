<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\ReferralTrack;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class AffiliateDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $affiliate = Affiliate::firstOrCreate(
            ['user_id' => $user->id],
            ['ref_code' => $this->generateAffiliateCode()]
        );

        // Hitung berdasarkan referral_tracks
        $totalClicks = ReferralTrack::where('ref_code', $affiliate->ref_code)->count();

        $totalJoins  = ReferralTrack::where('ref_code', $affiliate->ref_code)
            ->where('status', 'joined_bot')   // atau whereNotNull('prospect_telegram_id')
            ->count();

        $totalSales = AffiliatePayout::where('affiliate_ref', $affiliate->ref_code)
            ->count();

        $totalCommission = AffiliatePayout::where('affiliate_ref', $affiliate->ref_code)
            ->sum('commission');

        $recentSales = Sale::where('affiliate_ref', $affiliate->ref_code)
            ->latest()
            ->take(10)
            ->get();

        // DATA PROSPEK UNTUK TABEL dengan pagination, filter & search
        $perPage = request()->get('per_page', 10);
        $query = ReferralTrack::where('ref_code', $affiliate->ref_code);

        // Filter by search (username, email, phone)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('prospect_telegram_username', 'like', "%{$search}%")
                  ->orWhere('prospect_name', 'like', "%{$search}%")
                  ->orWhere('prospect_email', 'like', "%{$search}%")
                  ->orWhere('prospect_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by date range
        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $leads = $query->latest()
            ->paginate($perPage)
            ->appends(request()->except('page'));

        return view('dashboard', compact(
            'affiliate',
            'totalClicks',
            'totalJoins',
            'totalSales',
            'totalCommission',
            'recentSales',
            'leads',
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
