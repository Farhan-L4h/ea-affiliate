<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralTrack extends Model
{
    protected $fillable = [
        'prospect_email',
        'prospect_telegram_id',
        'prospect_ip',
        'ref_code',
    ];
}

