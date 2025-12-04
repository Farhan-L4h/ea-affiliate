<?php

namespace App\Http\Controllers;

use App\Models\ReferralTrack;
use Illuminate\Http\Request;

class ReferralTrackController extends Controller
{
    public function update(Request $request, ReferralTrack $lead)
    {
        // (opsional) pastikan ini lead milik affiliate yang login
        // kalau mau aman, cek ref_code terhadap affiliate user di sini

        $data = $request->validate([
            'prospect_email'  => ['nullable', 'email', 'max:120'],
            'prospect_phone'  => ['nullable', 'string', 'max:30'],
            'status'          => ['required', 'in:clicked,joined_bot,purchased'],
            'notes'           => ['nullable', 'string', 'max:255'],
        ]);

        $lead->update($data);

        return back()->with('success', 'Data prospek berhasil diperbarui.');
    }
}
