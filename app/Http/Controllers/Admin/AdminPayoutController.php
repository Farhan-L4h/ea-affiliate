<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliatePayout;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPayoutController extends Controller
{
    /**
     * Show payout requests list
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = AffiliatePayout::with(['affiliate.user'])
            ->where('request_type', 'manual')
            ->orderBy('created_at', 'desc');

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20);

        // Get counts for each status
        $pendingCount = AffiliatePayout::where('request_type', 'manual')
            ->where('status', 'pending')->count();
        $approvedCount = AffiliatePayout::where('request_type', 'manual')
            ->where('status', 'approved')->count();
        $paidCount = AffiliatePayout::where('request_type', 'manual')
            ->where('status', 'paid')->count();
        $rejectedCount = AffiliatePayout::where('request_type', 'manual')
            ->where('status', 'rejected')->count();

        return view('admin.payouts.index', compact(
            'payouts',
            'status',
            'pendingCount',
            'approvedCount',
            'paidCount',
            'rejectedCount'
        ));
    }

    /**
     * Show payout detail
     */
    public function show($id)
    {
        $payout = AffiliatePayout::with(['affiliate.user', 'processor'])
            ->findOrFail($id);

        return view('admin.payouts.show', compact('payout'));
    }

    /**
     * Approve payout request
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $payout = AffiliatePayout::with('affiliate')->findOrFail($id);

        if ($payout->status != 'pending') {
            return back()->with('error', 'Pengajuan ini tidak dapat disetujui');
        }

        DB::beginTransaction();
        try {
            $payout->update([
                'status' => 'approved',
                'admin_note' => $request->admin_note,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Pengajuan pencairan telah disetujui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject payout request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $payout = AffiliatePayout::with('affiliate')->findOrFail($id);

        if ($payout->status != 'pending') {
            return back()->with('error', 'Pengajuan ini tidak dapat ditolak');
        }

        DB::beginTransaction();
        try {
            $payout->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Pengajuan pencairan telah ditolak');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark as paid (after manual transfer)
     */
    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $payout = AffiliatePayout::with('affiliate')->findOrFail($id);

        if ($payout->status != 'approved') {
            return back()->with('error', 'Hanya pengajuan yang sudah disetujui yang dapat ditandai sebagai dibayar');
        }

        DB::beginTransaction();
        try {
            // Reduce total commission
            $affiliate = $payout->affiliate;
            $affiliate->update([
                'total_commission' => $affiliate->total_commission - $payout->amount,
            ]);

            $payout->update([
                'status' => 'paid',
                'admin_note' => $request->admin_note,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Pembayaran berhasil dikonfirmasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
