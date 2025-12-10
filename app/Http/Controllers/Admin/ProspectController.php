<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralTrack;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReferralTrack::with('affiliate.user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prospect_telegram_username', 'like', "%{$search}%")
                  ->orWhere('prospect_name', 'like', "%{$search}%")
                  ->orWhere('prospect_email', 'like', "%{$search}%")
                  ->orWhere('prospect_phone', 'like', "%{$search}%")
                  ->orWhere('ref_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by affiliate
        if ($request->filled('affiliate')) {
            $query->where('ref_code', $request->affiliate);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 25);
        $prospects = $query->latest()
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Get all affiliates for filter dropdown
        $affiliates = \App\Models\Affiliate::with('user')->get();

        return view('admin.prospects.index', compact('prospects', 'affiliates'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ReferralTrack $prospect)
    {
        $prospect->load('affiliate.user');
        return view('admin.prospects.show', compact('prospect'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReferralTrack $prospect)
    {
        $validated = $request->validate([
            'prospect_email'  => ['nullable', 'email', 'max:120'],
            'prospect_phone'  => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'status'          => ['required', 'in:clicked,joined_channel,purchased'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $prospect->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Prospek berhasil diupdate.'
            ]);
        }

        return back()->with('success', 'Prospek berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReferralTrack $prospect)
    {
        $prospect->delete();

        return back()->with('success', 'Prospek berhasil dihapus.');
    }

    /**
     * Bulk delete prospects
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:referral_tracks,id']
        ]);

        $count = ReferralTrack::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "{$count} prospek berhasil dihapus.");
    }
}
