<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AffiliateTracker
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('ref')) {
            $ref = strtoupper($request->query('ref'));

            // Kalau belum punya cookie, baru set (first click wins)
            if (! $request->cookies->has('affiliate_ref')) {
                // 90 hari (dalam menit)
                $minutes = 60 * 24 * 90;

                Cookie::queue('affiliate_ref', $ref, $minutes);

                // Tambah total_clicks affiliator
                Affiliate::where('ref_code', $ref)->increment('total_clicks');
            }
        }

        return $next($request);
    }
}

