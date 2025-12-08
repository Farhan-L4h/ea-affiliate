<?php

namespace App\Http\Controllers;

use App\Models\ReferralTrack;
use Illuminate\Http\Request;

class ReferralTrackController extends Controller
{
    public function update(Request $request, ReferralTrack $lead)
    {
        // Validasi input
        $validated = $request->validate([
            'prospect_email'  => ['nullable', 'email', 'max:120'],
            'prospect_phone'  => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'status'          => ['required', 'in:clicked,joined_channel,purchased'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ], [
            'prospect_email.email' => 'Format email tidak valid',
            'prospect_email.max' => 'Email maksimal 120 karakter',
            'prospect_phone.max' => 'Nomor telepon maksimal 30 karakter',
            'prospect_phone.regex' => 'Format nomor telepon tidak valid',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
            'notes.max' => 'Keterangan maksimal 500 karakter',
        ]);

        try {
            $lead->update($validated);

            // Return JSON response untuk AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data prospek berhasil diperbarui.'
                ]);
            }

            return back()->with('success', 'Data prospek berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengupdate data.');
        }
    }
}
