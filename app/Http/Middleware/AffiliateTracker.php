<?php
// app/Http/Middleware/AffiliateTracker.php
// app/Http/Middleware/AffiliateTracker.php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use App\Models\ReferralTrack;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AffiliateTracker
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('ref')) {
            $ref = strtoupper($request->query('ref'));

            // first click wins
            if (! $request->cookies->has('affiliate_ref')) {
                $minutes = 60 * 24 * 90; // 90 hari
                Cookie::queue('affiliate_ref', $ref, $minutes);

                Affiliate::where('ref_code', $ref)->increment('total_clicks');

                ReferralTrack::create([
                    'ref_code'    => $ref,
                    'prospect_ip' => $request->ip(),
                    'status'      => 'clicked',
                ]);
            }
        }

        return $next($request);
    }
}
