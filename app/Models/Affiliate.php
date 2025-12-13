<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'ref_code',
        'total_clicks',
        'total_joins',
        'total_sales',
        'commission_rate',
        'total_commission',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referralTracks()
    {
        return $this->hasMany(ReferralTrack::class, 'ref_code', 'ref_code');
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class, 'affiliate_id', 'id');
    }
}

