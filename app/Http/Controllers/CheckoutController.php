<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ReferralTrack;
use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // Validasi simple dulu
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120'],
        ]);

        $productName = 'EA HabaGridPro';
        $amount      = 1000000; // 1.000.000, nanti bisa kamu buat dynamic

        // 1. Cari affiliate_ref dari cookie (first click)
        $affiliateRef = $request->cookie('affiliate_ref');

        // 2. Kalau cookie kosong, cek referral_tracks by email
        if (! $affiliateRef) {
            $track = ReferralTrack::where('prospect_email', $data['email'])->first();
            if ($track) {
                $affiliateRef = $track->ref_code;
            }
        }

        // 3. Simpan referral_tracks (firstOrCreate = kunci pengundang pertama)
        ReferralTrack::firstOrCreate(
            ['prospect_email' => $data['email']],
            [
                'ref_code'    => $affiliateRef,
                'prospect_ip' => $request->ip(),
            ]
        );

        // 4. Simpan penjualan (sementara langsung 'paid')
        $sale = Sale::create([
            'user_id'       => null, // nanti bisa dihubungkan ke akun pembeli
            'affiliate_ref' => $affiliateRef,
            'product'       => $productName,
            'amount'        => $amount,
            'status'        => 'paid',
        ]);

        // 5. Buat komisi kalau ada affiliate
        if ($affiliateRef) {
            $commissionRate = 0.30; // 30%
            $commission     = $amount * $commissionRate;

            AffiliatePayout::create([
                'affiliate_ref' => $affiliateRef,
                'sale_id'       => $sale->id,
                'commission'    => $commission,
                'status'        => 'pending',
            ]);

            // update total_sales affiliator
            Affiliate::where('ref_code', $affiliateRef)->increment('total_sales');
        }

        return redirect('/')
            ->with('success', 'Pembelian tercatat. (Masih dummy, nanti kita sambung ke payment gateway)');
    }
}
