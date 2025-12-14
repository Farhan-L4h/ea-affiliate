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

            // First click wins - check if cookie already exists
            if (! $request->cookies->has('affiliate_ref')) {
                $minutes = 60 * 24 * 90; // 90 hari
                Cookie::queue('affiliate_ref', $ref, $minutes);

                // Increment total clicks for this affiliate
                Affiliate::where('ref_code', $ref)->increment('total_clicks');

                // Check if there's already a record from this IP
                $existingTrack = ReferralTrack::where('prospect_ip', $request->ip())
                    ->whereNotNull('prospect_ip')
                    ->first();

                // Only create if no existing record from this IP
                if (!$existingTrack) {
                    ReferralTrack::create([
                        'ref_code'    => $ref,
                        'prospect_ip' => $request->ip(),
                        'status'      => 'clicked',
                    ]);
                }
                // If exists, keep the original affiliate (first click wins)
            }
            // If cookie exists, ignore the new ref code (first click wins)
        }

        return $next($request);
    }
}
