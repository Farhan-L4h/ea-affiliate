<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'affiliate')->with('affiliate');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $perPage = $request->get('per_page', 10);
        $affiliates = $query->latest()
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('admin.affiliates.index', compact('affiliates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.affiliates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:25'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/', 'unique:users'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 25 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus berupa angka 10-15 digit.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'affiliate',
            'is_active' => $request->has('is_active'),
        ]);

        // Generate affiliate code
        $refCode = $this->generateAffiliateCode();
        Affiliate::create([
            'user_id' => $user->id,
            'ref_code' => $refCode,
        ]);

        return redirect()->route('admin.affiliates.show', $user)
            ->with('success', 'Affiliate berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $affiliate)
    {
        $affiliate->load(['affiliate.referralTracks', 'affiliate.payouts']);
        
        $stats = [
            'total_clicks' => $affiliate->affiliate->referralTracks()->count(),
            'total_joined' => $affiliate->affiliate->referralTracks()->where('status', 'joined_channel')->count(),
            'total_purchased' => $affiliate->affiliate->referralTracks()->where('status', 'purchased')->count(),
            'total_commission' => $affiliate->affiliate->payouts()->sum('amount'),
            'paid_commission' => $affiliate->affiliate->payouts()->where('status', 'paid')->sum('amount'),
        ];

        return view('admin.affiliates.show', compact('affiliate', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $affiliate)
    {
        return view('admin.affiliates.edit', compact('affiliate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $affiliate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/', 'unique:users,phone,' . $affiliate->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $affiliate->id],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus berupa angka 10-15 digit.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $affiliate->update($data);

        return redirect()->route('admin.affiliates.show', $affiliate)
            ->with('success', 'Affiliate berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $affiliate)
    {
        // Cek apakah akun sudah nonaktif
        if ($affiliate->is_active) {
            return redirect()->route('admin.affiliates.show', $affiliate)
                ->with('error', 'Akun harus dinonaktifkan terlebih dahulu sebelum dihapus.');
        }

        // Hapus data affiliate
        if ($affiliate->affiliate) {
            $affiliate->affiliate->delete();
        }
        
        $affiliate->delete();

        return redirect()->route('admin.affiliates.index')
            ->with('success', 'Affiliate berhasil dihapus.');
    }

    /**
     * Toggle affiliate status
     */
    public function toggleStatus(User $affiliate)
    {
        $affiliate->update(['is_active' => !$affiliate->is_active]);

        $status = $affiliate->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Affiliate berhasil {$status}.");
    }

    /**
     * Generate unique affiliate code
     */
    private function generateAffiliateCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (Affiliate::where('ref_code', $code)->exists());

        return $code;
    }
}
