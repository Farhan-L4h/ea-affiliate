<?php

/**
 * Force Reset Telegram Webhook
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$botToken = config('services.telegram.bot_token');
$webhookUrl = config('services.telegram.webhook_url');

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         FORCE RESET TELEGRAM WEBHOOK                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Bot Token: " . substr($botToken, 0, 15) . "...\n";
echo "Webhook URL: {$webhookUrl}\n\n";

// Step 1: Delete webhook
echo "🗑️  STEP 1: Menghapus webhook yang ada...\n";
$deleteResponse = Http::post("https://api.telegram.org/bot{$botToken}/deleteWebhook")->json();
echo "   Result: " . ($deleteResponse['ok'] ? '✅ Deleted' : '❌ Failed') . "\n";
echo "   Description: " . ($deleteResponse['description'] ?? 'N/A') . "\n\n";

sleep(2);

// Step 2: Set webhook dengan allowed_updates
echo "🔧 STEP 2: Set webhook dengan konfigurasi lengkap...\n";
$setResponse = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
    'url' => $webhookUrl,
    'allowed_updates' => json_encode(['message', 'callback_query', 'chat_member', 'my_chat_member']),
    'drop_pending_updates' => true, // Hapus pending updates lama
])->json();

echo "   Result: " . ($setResponse['ok'] ? '✅ Success' : '❌ Failed') . "\n";
echo "   Description: " . ($setResponse['description'] ?? 'N/A') . "\n\n";

sleep(2);

// Step 3: Verify
echo "✅ STEP 3: Verifikasi webhook info...\n";
$infoResponse = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo")->json();

if ($infoResponse['ok']) {
    $info = $infoResponse['result'];
    
    echo "   URL: " . ($info['url'] ?: '(not set)') . "\n";
    echo "   Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    
    if (isset($info['last_error_date'])) {
        echo "   ⚠️  Last error: " . $info['last_error_message'] . "\n";
        echo "   Last error date: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
    } else {
        echo "   ✅ No errors\n";
    }
    
    if (isset($info['allowed_updates'])) {
        echo "   Allowed updates: " . implode(', ', $info['allowed_updates']) . "\n";
        
        if (in_array('chat_member', $info['allowed_updates'])) {
            echo "   ✅ chat_member INCLUDED!\n";
        } else {
            echo "   ❌ chat_member NOT included!\n";
        }
    }
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    NEXT STEPS                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "1. Monitor log real-time:\n";
echo "   tail -f storage/logs/laravel.log\n\n";
echo "2. Test dengan user BARU yang belum pernah join:\n";
echo "   - Klik link affiliate\n";
echo "   - /start di bot\n";
echo "   - Join channel @scalpermaxproai\n\n";
echo "3. Harus muncul log 'chat_member' di monitoring\n";
echo "4. Status otomatis berubah ke 'joined_channel'\n\n";

if (isset($info['last_error_date'])) {
    echo "⚠️  CATATAN: Masih ada error terakhir pada webhook.\n";
    echo "   Upload file TelegramWebhookController.php yang sudah diperbaiki!\n\n";
}
