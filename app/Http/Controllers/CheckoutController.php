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
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $productName = 'EA HabaGridPro';
        $amount      = 1000000;

        // 1. Cari affiliateRef dari cookie dulu
        $affiliateRef = $request->cookie('affiliate_ref');

        // 2. Cek referral_track by email (first inviter wins)
        $track = ReferralTrack::where('prospect_email', $data['email'])->first();

        if ($track) {
            // Pakai ref_code yang sudah tersimpan (jangan diganti)
            $affiliateRef = $track->ref_code;
        }

        // 3. Kalau belum ada track, buat baru
        if (! $track) {
            $track = ReferralTrack::create([
                'prospect_name'  => $data['name'],
                'prospect_email' => $data['email'],
                'prospect_phone' => $data['phone'] ?? null,
                'prospect_ip'    => $request->ip(),
                'ref_code'       => $affiliateRef,
                'status'         => 'purchased',
            ]);
        } else {
            // update data tambahan + status
            $track->prospect_name  = $data['name'];
            $track->prospect_phone = $data['phone'] ?? $track->prospect_phone;

            if ($track->status !== 'purchased') {
                $track->status = 'purchased';
            }

            $track->save();
        }

        // 4. Simpan sales
        $sale = Sale::create([
            'user_id'       => null,
            'affiliate_ref' => $affiliateRef,
            'product'       => $productName,
            'amount'        => $amount,
            'status'        => 'paid',
        ]);

        // 5. Komisi affiliate
        if ($affiliateRef) {
            $commissionRate = 0.30;
            $commission     = $amount * $commissionRate;

            AffiliatePayout::create([
                'affiliate_ref' => $affiliateRef,
                'sale_id'       => $sale->id,
                'commission'    => $commission,
                'status'        => 'pending',
            ]);

            Affiliate::where('ref_code', $affiliateRef)->increment('total_sales');
        }

        return redirect('/')
            ->with('success', 'Pembelian tercatat (dummy). Nanti tinggal sambung ke payment gateway.');
    }
}
