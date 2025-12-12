<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'telegram_chat_id',
        'telegram_username',
        'affiliate_ref',
        'product',
        'base_amount',
        'unique_code',
        'total_amount',
        'status',
        'payment_method',
        'payment_info',
        'moota_tagging_id',
        'paid_at',
        'expired_at',
        'note',
    ];

    protected $casts = [
        'payment_info' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'base_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_ref', 'ref_code');
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Check if order is expired
     */
    public function isExpired(): bool
    {
        if ($this->expired_at && Carbon::now()->greaterThan($this->expired_at)) {
            return true;
        }
        return false;
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark order as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Generate order ID
     */
    public static function generateOrderId(): string
    {
        return 'ORDER-' . strtoupper(uniqid());
    }
}
