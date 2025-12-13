<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Payload dari Moota
$mutation = [
    "account_number" => "042901012282533",
    "date" => "2025-12-13 00:00:00",
    "description" => null,
    "amount" => 1,
    "type" => "CR",
    "note" => null,
    "items" => [
        [
            "name" => "ORDER-693D0AF6DD4D2",
            "description" => "ORDER-693D0AF6DD4D2",
            "qty" => 1,
            "price" => 1.300461,
        ]
    ],
];

echo "Testing extractOrderId...\n\n";

// Simulate extraction
$orderId = null;

// Check in items array
$items = $mutation['items'] ?? [];
echo "Items found: " . count($items) . "\n";

if (is_array($items)) {
    foreach ($items as $item) {
        echo "Checking item...\n";
        echo "  name: " . ($item['name'] ?? 'null') . "\n";
        echo "  description: " . ($item['description'] ?? 'null') . "\n";
        
        // Check in item name
        if (preg_match('/ORDER-[A-Z0-9]+/i', $item['name'] ?? '', $matches)) {
            $orderId = strtoupper($matches[0]);
            echo "  ✅ Found in name: {$orderId}\n";
            break;
        }
        
        // Check in item description
        if (preg_match('/ORDER-[A-Z0-9]+/i', $item['description'] ?? '', $matches)) {
            $orderId = strtoupper($matches[0]);
            echo "  ✅ Found in description: {$orderId}\n";
            break;
        }
    }
}

if ($orderId) {
    echo "\n✅ Order ID extracted: {$orderId}\n";
} else {
    echo "\n❌ Order ID NOT found!\n";
}
