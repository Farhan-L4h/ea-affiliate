<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/ReferralTrack.php

class ReferralTrack extends Model
{
    protected $fillable = [
        'prospect_name',
        'prospect_email',
        'prospect_phone',
        'prospect_telegram_id',
        'prospect_telegram_username',
        'prospect_ip',
        'ref_code',
        'status',
        'notes', // ⬅️ tambahin ini
    ];
}
