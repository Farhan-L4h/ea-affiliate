<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'affiliate_id',
        'product',
        'sale_amount',
        'commission_percentage',
        'commission_amount',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'sale_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class);
    }
}

