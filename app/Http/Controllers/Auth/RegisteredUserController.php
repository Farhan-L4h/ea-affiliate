<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Affiliate;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // === AUTO BUAT AFFILIATE UNTUK USER INI ===
        $refCode = $this->generateAffiliateCode();

        Affiliate::create([
            'user_id' => $user->id,
            'ref_code' => $refCode,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard'); // langsung ke dashboard affiliate
    }

    private function generateAffiliateCode(): string
    {
        do {
            // contoh: HAB123ABC
            $code = 'AF' . strtoupper(str()->random(6));
        } while (Affiliate::where('ref_code', $code)->exists());

        return $code;
    }
}
