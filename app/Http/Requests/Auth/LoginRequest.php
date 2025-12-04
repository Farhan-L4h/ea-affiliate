<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Cek apakah user dengan phone ini ada
        $user = \App\Models\User::where('phone', $this->phone)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => 'Nomor telepon belum terdaftar. Silakan hubungi admin untuk registrasi.',
            ]);
        }

        // User ada, coba login
        if (! Auth::attempt(
            ['phone' => $this->phone, 'password' => $this->password],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => 'Password Atau nomor telepon yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Cek & lempar error kalau sudah terlalu banyak percobaan login gagal
     */
    protected function ensureIsNotRateLimited(): void
    {
        // 5 = maksimal percobaan sebelum ke-lock
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'phone' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Key unik untuk rate limit (gabungan phone + IP)
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->input('phone')).'|'.$this->ip()
        );
    }
}
