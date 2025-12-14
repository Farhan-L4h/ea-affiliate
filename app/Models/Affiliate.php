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
        'bank_name',
        'account_holder_name',
        'account_number',
        'commission_rate',
        'total_commission',
        'available_balance',
        'withdrawn_balance',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'withdrawn_balance' => 'decimal:2',
    ];

    /**
     * Check if affiliate has completed bank information
     */
    public function hasBankInfo(): bool
    {
        return !empty($this->bank_name) && 
               !empty($this->account_holder_name) && 
               !empty($this->account_number);
    }

    /**
     * Get pending payout requests
     */
    public function pendingPayouts()
    {
        return $this->payouts()->where('status', 'pending');
    }

    /**
     * Get approved payouts
     */
    public function approvedPayouts()
    {
        return $this->payouts()->where('status', 'approved');
    }

    /**
     * Get paid payouts
     */
    public function paidPayouts()
    {
        return $this->payouts()->where('status', 'paid');
    }

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

