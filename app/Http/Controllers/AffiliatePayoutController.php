<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffiliatePayoutController extends Controller
{
    /**
     * Show payout dashboard for affiliate
     */
    public function index()
    {
        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return redirect()->route('dashboard')->with('error', 'Affiliate tidak ditemukan');
        }

        // Get payout history
        $payoutHistory = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->where('request_type', 'manual')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('affiliate.payout.index', compact('affiliate', 'payoutHistory'));
    }

    /**
     * Show payout detail
     */
    public function show($id)
    {
        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return redirect()->route('dashboard')->with('error', 'Affiliate tidak ditemukan');
        }

        $payout = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->where('id', $id)
            ->firstOrFail();

        return view('affiliate.payout.show', compact('payout'));
    }

    /**
     * Show bank information form
     */
    public function bankInfo()
    {
        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return redirect()->route('dashboard')->with('error', 'Affiliate tidak ditemukan');
        }

        return view('affiliate.payout.bank-info', compact('affiliate'));
    }

    /**
     * Update bank information
     */
    public function updateBankInfo(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
        ]);

        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return redirect()->route('dashboard')->with('error', 'Affiliate tidak ditemukan');
        }

        $affiliate->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
        ]);

        return redirect()->route('affiliate.payout.index')
            ->with('success', 'Data bank berhasil diperbarui');
    }

    /**
     * Request payout
     */
    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000', // Minimum payout 10,000
        ]);

        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return back()->with('error', 'Affiliate tidak ditemukan');
        }

        // Check if bank info is complete
        if (!$affiliate->hasBankInfo()) {
            return back()->with('error', 'Harap lengkapi data bank terlebih dahulu');
        }

        // Check if has enough balance
        if ($affiliate->available_balance < $request->amount) {
            return back()->with('error', 'Saldo tidak mencukupi');
        }

        // Check if there's pending request
        $hasPending = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->where('request_type', 'manual')
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Anda masih memiliki pengajuan pencairan yang sedang diproses');
        }

        DB::beginTransaction();
        try {
            // Create payout request
            AffiliatePayout::create([
                'affiliate_id' => $affiliate->id,
                'sale_id' => null,
                'amount' => $request->amount,
                'status' => 'pending',
                'request_type' => 'manual',
                'requested_at' => now(),
            ]);

            // Update available balance (hold the amount)
            $affiliate->update([
                'available_balance' => $affiliate->available_balance - $request->amount,
            ]);

            DB::commit();

            return back()->with('success', 'Pengajuan pencairan komisi berhasil diajukan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel payout request
     */
    public function cancelRequest($id)
    {
        $user = Auth::user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            return back()->with('error', 'Affiliate tidak ditemukan');
        }

        $payout = AffiliatePayout::where('id', $id)
            ->where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')
            ->first();

        if (!$payout) {
            return back()->with('error', 'Pengajuan tidak ditemukan atau tidak dapat dibatalkan');
        }

        DB::beginTransaction();
        try {
            // Return balance
            $affiliate->update([
                'available_balance' => $affiliate->available_balance + $payout->amount,
            ]);

            // Delete request
            $payout->delete();

            DB::commit();

            return back()->with('success', 'Pengajuan pencairan berhasil dibatalkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
