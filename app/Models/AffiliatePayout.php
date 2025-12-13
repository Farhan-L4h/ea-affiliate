<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_id',
        'sale_id',
        'amount',
        'status',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

