<?php

/**
 * Test Telegram Bot - Quick Check
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Telegram Bot Configuration ===\n\n";

// Check config
$token = config('services.telegram.bot_token');
$username = config('services.telegram.bot_username');

echo "Bot Token: " . (empty($token) ? "❌ NOT SET" : "✅ " . substr($token, 0, 10) . "...") . "\n";
echo "Bot Username: " . ($username ?? "❌ NOT SET") . "\n\n";

if (empty($token)) {
    echo "⚠️  TELEGRAM_BOT_TOKEN tidak ada di .env!\n";
    echo "Tambahkan ke .env:\n";
    echo "TELEGRAM_BOT_TOKEN=your_bot_token_here\n\n";
    exit(1);
}

// Get chat ID from command line
$chatId = $argv[1] ?? null;

if (!$chatId) {
    echo "Usage: php test_telegram_local.php YOUR_CHAT_ID\n";
    echo "Dapatkan chat ID dengan:\n";
    echo "1. Buka https://t.me/" . ($username ?? 'your_bot') . "\n";
    echo "2. Klik /start\n";
    echo "3. Cek log atau kirim pesan, lihat chat_id di response\n\n";
    exit(1);
}

echo "Testing send message to chat ID: {$chatId}\n\n";

// Test send message
use App\Services\TelegramService;

try {
    $telegram = app(TelegramService::class);
    
    $message = "🧪 Test pesan dari LOCAL environment\n\n";
    $message .= "Waktu: " . now() . "\n";
    $message .= "Jika kamu menerima pesan ini, berarti bot berfungsi! ✅";
    
    $telegram->sendMessage($chatId, $message);
    
    echo "✅ Pesan berhasil dikirim!\n";
    echo "Cek Telegram kamu sekarang.\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Test Selesai ===\n";
echo "Jika pesan tidak masuk, cek:\n";
echo "1. Bot token benar\n";
echo "2. Chat ID benar\n";
echo "3. Bot sudah di-/start oleh user\n";
