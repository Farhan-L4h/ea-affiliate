<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_ref',
        'sale_id',
        'commission',
        'status',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

