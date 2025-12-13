<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$orderId = 'ORDER-693CFEE03A7D4';

echo "🔄 Testing webhook for: {$orderId}\n\n";

// Find order
$order = \App\Models\Order::where('order_id', $orderId)->first();

if (!$order) {
    echo "❌ Order not found!\n";
    exit(1);
}

echo "Order Status Before: {$order->status}\n\n";

// Simulate webhook data (format sandbox Moota)
$webhookData = [
    'amount' => (string) $order->total_amount,
    'type' => 'CR',
    'description' => null,
    'note' => null,
    'items' => [
        [
            'name' => $order->order_id,
            'description' => $order->order_id,
            'qty' => 1,
            'price' => $order->base_amount,
        ]
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
        
        echo "Order Status After: {$order->status}\n";
        echo "Paid At: " . ($order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '-') . "\n\n";
        
        // Check sale
        $sale = $order->sale;
        if ($sale) {
            echo "✅ Sale Record Created:\n";
            echo "   Sale ID: {$sale->id}\n";
            echo "   Amount: Rp " . number_format($sale->sale_amount, 0, ',', '.') . "\n";
            echo "   Commission: Rp " . number_format($sale->commission_amount, 0, ',', '.') . "\n\n";
        }
        
        echo "Response: {$response->getContent()}\n";
    } else {
        echo "❌ Webhook failed!\n";
        echo "Status: {$response->getStatusCode()}\n";
        echo "Response: {$response->getContent()}\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
