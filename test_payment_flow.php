<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║    MOOTA PAYMENT WEBHOOK - SANDBOX TESTING TOOL          ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Step 1: Create Test Order
echo "📝 Step 1: Creating test order...\n";
$order = new \App\Models\Order();
$order->order_id = 'ORDER-TEST' . strtoupper(uniqid());
$order->telegram_chat_id = '7035692786';
$order->telegram_username = 'TestUser';
$order->affiliate_ref = 'AFRATFYI'; // Sesuaikan dengan ref_code affiliate yang ada
$order->product = 'EA Scalper Cepat MT5';
$order->base_amount = 10000;
$order->unique_code = mt_rand(1, 999);
$order->total_amount = $order->base_amount + $order->unique_code;
$order->status = 'pending';
$order->payment_method = 'bank_transfer';
$order->payment_info = [
    'bank' => 'BCA',
    'account_number' => '0111502977',
    'account_name' => 'Udin Nurwachid'
];
$order->expired_at = now()->addDay();
$order->save();

echo "✅ Order created!\n";
echo "   Order ID: {$order->order_id}\n";
echo "   Base Amount: Rp " . number_format($order->base_amount, 0, ',', '.') . "\n";
echo "   Unique Code: {$order->unique_code}\n";
echo "   Total Amount: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n\n";

// Step 2: Simulate Payment
echo "💳 Step 2: Simulating payment webhook...\n";

$webhookData = [
    'amount' => (string) $order->total_amount,
    'type' => 'CR',
    'description' => "Transfer dari customer untuk {$order->order_id}",
    'note' => $order->order_id,
    'tags' => [
        ['name' => $order->order_id]
    ],
];

$request = \Illuminate\Http\Request::create('/webhook/moota', 'POST', $webhookData);

try {
    $controller = app(\App\Http\Controllers\MootaWebhookController::class);
    $response = $controller->handle($request);
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Webhook processed successfully!\n\n";
        
        // Reload order
        $order->refresh();
        
        // Step 3: Verify Results
        echo "📊 Step 3: Verification Results\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        echo "Order Status:\n";
        echo "   Status: {$order->status}\n";
        echo "   Paid At: " . ($order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : 'Not set') . "\n\n";
        
        // Check sale
        $sale = $order->sale;
        if ($sale) {
            echo "✅ Sale Record Created:\n";
            echo "   Sale ID: {$sale->id}\n";
            echo "   Product: {$sale->product}\n";
            echo "   Sale Amount: Rp " . number_format($sale->sale_amount, 0, ',', '.') . "\n";
            echo "   Commission: Rp " . number_format($sale->commission_amount, 0, ',', '.') . " ({$sale->commission_percentage}%)\n";
            echo "   Sale Date: {$sale->sale_date}\n\n";
        } else {
            echo "❌ Sale record not found!\n\n";
        }
        
        // Check payout
        if ($sale) {
            $payout = \App\Models\AffiliatePayout::where('sale_id', $sale->id)->first();
            if ($payout) {
                echo "✅ Affiliate Payout Created:\n";
                echo "   Payout ID: {$payout->id}\n";
                echo "   Affiliate ID: {$payout->affiliate_id}\n";
                echo "   Amount: Rp " . number_format($payout->amount, 0, ',', '.') . "\n";
                echo "   Status: {$payout->status}\n\n";
            } else {
                echo "ℹ️  No affiliate payout (order mungkin tidak ada affiliate_ref)\n\n";
            }
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🎉 Testing completed successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
    } else {
        echo "❌ Webhook failed!\n";
        echo "Status: {$response->getStatusCode()}\n";
        echo "Response: {$response->getContent()}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
