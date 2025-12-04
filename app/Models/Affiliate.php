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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

