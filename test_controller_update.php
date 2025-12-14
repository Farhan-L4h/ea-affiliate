<?php

/**
 * Test apakah TelegramWebhookController sudah terupdate
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\TelegramWebhookController;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║    TEST APAKAH CONTROLLER SUDAH TERUPDATE                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Simulasi chat_member update
$testData = [
    'update_id' => 999999999,
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
            'first_name' => 'Bot'
        ],
        'date' => time(),
        'old_chat_member' => [
            'user' => [
                'id' => 7658777366, // Monitabisnis
                'is_bot' => false,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'status' => 'left'
        ],
        'new_chat_member' => [
            'user' => [
                'id' => 7658777366,
                'is_bot' => false,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'status' => 'member'
        ]
    ]
];

echo "📦 Test Data (chat_member update):\n";
echo "   User ID: 7658777366 (Monitabisnis)\n";
echo "   Channel ID: " . config('services.telegram.group_id') . "\n";
echo "   Status: left → member\n\n";

echo "🚀 Calling webhook handler...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Create request
    $request = Request::create('/telegram/webhook', 'POST', $testData);
    
    // Call controller
    $controller = new TelegramWebhookController();
    $response = $controller->handle($request);
    
    echo "✅ Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getContent() . "\n\n";
    
    // Check log file
    echo "📋 Checking last 20 lines of log...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        
        foreach ($lastLines as $line) {
            if (stripos($line, 'chat_member') !== false || 
                stripos($line, 'joined_channel') !== false ||
                stripos($line, 'Status updated') !== false) {
                echo $line;
            }
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR!\n\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
echo "💡 YANG HARUS MUNCUL DI LOG:\n";
echo "   - 'Telegram update received' dengan 'chat_member'\n";
echo "   - 'Status updated to joined_channel' (INI PALING PENTING!)\n";
echo "\n";
echo "Jika TIDAK muncul 'Status updated', berarti:\n";
echo "1. Cache belum clear → php artisan optimize:clear\n";
echo "2. File belum terupload → upload lagi\n";
echo "3. Ada kondisi yang tidak terpenuhi → cek debug log\n";
echo "\n";
