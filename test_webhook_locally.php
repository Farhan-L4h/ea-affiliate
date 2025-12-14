<?php

/**
 * Test Webhook Handler Locally
 * Simulate Telegram webhook untuk testing tanpa perlu Telegram asli
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TelegramWebhookController;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         TEST TELEGRAM WEBHOOK - CHAT MEMBER UPDATE             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Simulasi data chat_member update dari Telegram
$testData = [
    'update_id' => 123456789,
    'chat_member' => [
        'chat' => [
            'id' => (int) config('services.telegram.group_id'),
            'title' => 'Scalper Max Pro',
            'type' => 'channel',
            'username' => 'scalpermaxproai'
        ],
        'from' => [
            'id' => 8584745617,
            'is_bot' => true,
            'first_name' => 'Desa Trading',
            'username' => 'desatrading_bot'
        ],
        'date' => time(),
        'old_chat_member' => [
            'user' => [
                'id' => 999999999,
                'is_bot' => false,
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser'
            ],
            'status' => 'left'
        ],
        'new_chat_member' => [
            'user' => [
                'id' => 999999999,
                'is_bot' => false,
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser'
            ],
            'status' => 'member'
        ]
    ]
];

echo "📦 Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

echo "🚀 Menjalankan webhook handler...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Create request
    $request = Request::create('/telegram/webhook', 'POST', $testData);
    
    // Call webhook controller
    $controller = new TelegramWebhookController();
    $response = $controller->handle($request);
    
    echo "✅ Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getContent() . "\n\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Webhook handler berjalan sukses!\n";
    } else {
        echo "⚠️  Webhook handler return status code: " . $response->getStatusCode() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR TERJADI!\n\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "💡 CATATAN:\n";
echo "- Jika ada error, perbaiki kode berdasarkan error message di atas\n";
echo "- Setelah fix, test lagi dengan script ini\n";
echo "- Kalau sudah OK, baru test dengan user real di Telegram\n";
echo "\n";
