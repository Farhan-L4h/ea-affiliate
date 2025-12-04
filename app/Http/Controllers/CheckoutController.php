<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Validasi minimal
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120'],
        ]);

        $productName = 'EA HabaGridPro';
        $amount = 1000000; // contoh 1 juta

        // 1. Cari affiliate_ref dari cookie dulu
        $affiliateRef = $request->cookie('affiliate_ref');

        // 2. Kalau cookie tidak ada, cek di referral_tracks by email
        if (! $affiliateRef) {
            $track = ReferralTrack::where('prospect_email', $data['email'])->first();
            if ($track) {
                $affiliateRef = $track->ref_code;
            }
        }

        // 3. Simpan referral_tracks (first click wins logic di DB)
        ReferralTrack::firstOrCreate(
            ['prospect_email' => $data['email']],
            [
                'ref_code'     => $affiliateRef,
                'prospect_ip'  => $request->ip(),
            ]
        );

        // 4. Simpan penjualan (asumsikan langsung paid, nanti bisa diupdate dari webhook)
        $sale = Sale::create([
            'user_id'       => null, // kalau ada sistem auth, bisa diisi
            'affiliate_ref' => $affiliateRef,
            'product'       => $productName,
            'amount'        => $amount,
            'status'        => 'paid',
        ]);

        // 5. Buat komisi kalau ada affiliate
        if ($affiliateRef) {
            $commissionRate = 0.30; // 30% misalnya
            $commission = $amount * $commissionRate;

            AffiliatePayout::create([
                'affiliate_ref' => $affiliateRef,
                'sale_id'       => $sale->id,
                'commission'    => $commission,
                'status'        => 'pending',
            ]);

            // update total_sales affiliator
            Affiliate::where('ref_code', $affiliateRef)->increment('total_sales');
        }

        return back()->with('success', 'Pembelian tercatat. Nanti tinggal sambung ke payment gateway & pengiriman EA.');
    }
}

