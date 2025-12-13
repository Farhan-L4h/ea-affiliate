<?php

/**
 * Test Moota Webhook Handler
 * 
 * This script simulates a Moota webhook call to test the payment verification
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

// Get order ID from command line argument
$orderId = $argv[1] ?? null;

if (!$orderId) {
    echo "Usage: php test_moota_webhook.php ORDER-XXXXXX\n";
    exit(1);
}

echo "Testing webhook for order: {$orderId}\n";
echo "Fetching mutation data from Moota...\n\n";

// Fetch mutation from Moota API
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . config('services.moota.token'),
    'Accept' => 'application/json',
])->get(config('services.moota.api_url') . '/mutation', [
    'type' => 'CR',
    'per_page' => 100,
]);

if (!$response->successful()) {
    echo "Failed to fetch mutations from Moota\n";
    exit(1);
}

$mutations = $response->json('data', []);
$matchingMutation = null;

// Find matching mutation
foreach ($mutations as $mutation) {
    $description = strtoupper($mutation['description'] ?? '');
    $note = strtoupper($mutation['note'] ?? '');
    
    // Check description and note
    if (str_contains($description, $orderId) || str_contains($note, $orderId)) {
        $matchingMutation = $mutation;
        break;
    }
    
    // Check items array
    $items = $mutation['items'] ?? [];
    if (is_array($items)) {
        foreach ($items as $item) {
            $itemName = strtoupper($item['name'] ?? '');
            $itemDesc = strtoupper($item['description'] ?? '');
            
            if (str_contains($itemName, $orderId) || str_contains($itemDesc, $orderId)) {
                $matchingMutation = $mutation;
                break 2;
            }
        }
    }
}

if (!$matchingMutation) {
    echo "❌ No matching mutation found for order: {$orderId}\n";
    exit(1);
}

echo "✅ Found matching mutation:\n";
echo "   Amount: Rp " . number_format($matchingMutation['amount'], 0, ',', '.') . "\n";
echo "   Date: {$matchingMutation['date']}\n";
echo "   Type: {$matchingMutation['type']}\n\n";

// Send webhook request to local endpoint
echo "Sending webhook request...\n";

$webhookUrl = config('app.url') . '/webhook/moota';
$webhookResponse = Http::post($webhookUrl, [$matchingMutation]);

echo "\nWebhook Response:\n";
echo "Status: " . $webhookResponse->status() . "\n";
echo "Body: " . $webhookResponse->body() . "\n";

if ($webhookResponse->successful()) {
    echo "\n✅ Webhook test successful!\n";
} else {
    echo "\n❌ Webhook test failed!\n";
}
