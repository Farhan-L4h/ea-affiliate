<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create test order
$order = new \App\Models\Order();
$order->order_id = 'ORDER-TEST' . strtoupper(uniqid());
$order->telegram_chat_id = '7035692786';
$order->telegram_username = 'FarhanL4h';
$order->affiliate_ref = 'AFRATFYI';
$order->product = 'EA Scalper Cepat MT5';
$order->base_amount = 10000;
$order->unique_code = 123;
$order->total_amount = 10123;
$order->status = 'pending';
$order->payment_method = 'bank_transfer';
$order->payment_info = ['bank' => 'BCA', 'account_number' => '0111502977', 'account_name' => 'Udin Nurwachid'];
$order->expired_at = now()->addDay();
$order->save();

echo "✅ Order created successfully!\n";
echo "Order ID: {$order->order_id}\n";
echo "Total Amount: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
