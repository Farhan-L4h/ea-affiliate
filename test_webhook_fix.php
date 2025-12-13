<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$orderId = 'ORDER-693D0F841B00D';

echo "🔄 Processing payment for: {$orderId}\n\n";

$order = \App\Models\Order::where('order_id', $orderId)->first();

if (!$order) {
    echo "❌ Order not found!\n";
    exit(1);
}

echo "Order Status Before: {$order->status}\n";
echo "Total Amount: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n\n";

// Simulate webhook dengan format array (seperti Moota kirim)
$webhookData = [[
    'amount' => (float) $order->total_amount,
    'type' => 'CR',
    'items' => [
        [
            'name' => $order->order_id,
            'description' => $order->order_id,
            'price' => (float) $order->base_amount,
        ]
    ],
]];

$request = \Illuminate\Http\Request::create('/webhook/moota', 'POST', $webhookData[0]);

try {
    $controller = app(\App\Http\Controllers\MootaWebhookController::class);
    $response = $controller->handle($request);
    
    if ($response->getStatusCode() === 200) {
        $order->refresh();
        
        echo "✅ Payment processed!\n\n";
        echo "Status: {$order->status}\n";
        echo "Paid At: " . ($order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '-') . "\n\n";
        
        $sale = $order->sale;
        if ($sale) {
            echo "✅ Sale Record:\n";
            echo "   ID: {$sale->id}\n";
            echo "   Amount: Rp " . number_format($sale->sale_amount, 0, ',', '.') . "\n";
            echo "   Commission: Rp " . number_format($sale->commission_amount, 0, ',', '.') . "\n\n";
        }
        
        echo "Response: {$response->getContent()}\n\n";
        echo "🎉 Done! Cek Telegram untuk notifikasi.\n";
    } else {
        echo "❌ Failed: {$response->getContent()}\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
