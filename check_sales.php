<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RECENT SALES ===\n\n";
$sales = \App\Models\Sale::with(['order', 'affiliate'])
    ->latest()
    ->take(3)
    ->get();

foreach ($sales as $sale) {
    echo "Sale ID: {$sale->id}\n";
    echo "Order ID: " . ($sale->order ? $sale->order->order_id : 'N/A') . "\n";
    echo "Product: {$sale->product}\n";
    echo "Sale Amount: Rp " . number_format($sale->sale_amount, 0, ',', '.') . "\n";
    echo "Commission: Rp " . number_format($sale->commission_amount, 0, ',', '.') . " ({$sale->commission_percentage}%)\n";
    echo "Affiliate: " . ($sale->affiliate ? $sale->affiliate->name : 'N/A') . "\n";
    echo "Date: {$sale->sale_date}\n";
    echo "---\n\n";
}

echo "\n=== AFFILIATE PAYOUTS ===\n\n";
$payouts = \App\Models\AffiliatePayout::with(['affiliate', 'sale'])
    ->latest()
    ->take(3)
    ->get();

foreach ($payouts as $payout) {
    echo "Payout ID: {$payout->id}\n";
    echo "Affiliate: " . ($payout->affiliate ? $payout->affiliate->name : 'N/A') . "\n";
    echo "Sale ID: {$payout->sale_id}\n";
    echo "Amount: Rp " . number_format($payout->amount, 0, ',', '.') . "\n";
    echo "Status: {$payout->status}\n";
    echo "---\n\n";
}
