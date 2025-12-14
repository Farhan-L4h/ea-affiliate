<?php

/**
 * Script untuk mendapatkan Chat ID dari channel/grup Telegram
 * 
 * CARA PAKAI:
 * 1. Tambahkan bot ke channel/grup
 * 2. Kirim pesan apa saja ke channel/grup
 * 3. Jalankan script ini: php get_channel_id.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$botToken = config('services.telegram.bot_token');

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║            MENDAPATKAN CHAT ID CHANNEL/GRUP                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "CARA PAKAI:\n";
echo "1. Pastikan bot sudah ditambahkan ke channel/grup\n";
echo "2. Kirim pesan apa saja ke channel/grup tersebut\n";
echo "3. Script ini akan menampilkan Chat ID\n";
echo "\n";
echo "Mengambil update terbaru...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get updates
$response = Http::get("https://api.telegram.org/bot{$botToken}/getUpdates")->json();

if (!($response['ok'] ?? false)) {
    echo "❌ Gagal mengambil updates\n";
    echo "Error: " . ($response['description'] ?? 'Unknown') . "\n";
    exit(1);
}

$updates = $response['result'] ?? [];

if (empty($updates)) {
    echo "⚠️  Tidak ada update ditemukan.\n\n";
    echo "SOLUSI:\n";
    echo "1. Pastikan bot sudah ditambahkan ke channel/grup\n";
    echo "2. Kirim pesan ke channel/grup (mention bot atau pesan biasa)\n";
    echo "3. Jalankan script ini lagi\n\n";
    exit(0);
}

echo "Ditemukan " . count($updates) . " update(s)\n\n";

$foundChats = [];

foreach ($updates as $update) {
    // Dari message biasa
    if (isset($update['message']['chat'])) {
        $chat = $update['message']['chat'];
        $chatId = $chat['id'];
        
        if (!isset($foundChats[$chatId])) {
            $foundChats[$chatId] = [
                'id' => $chatId,
                'type' => $chat['type'],
                'title' => $chat['title'] ?? ($chat['first_name'] ?? 'Unknown'),
                'username' => $chat['username'] ?? null,
            ];
        }
    }
    
    // Dari channel post
    if (isset($update['channel_post']['chat'])) {
        $chat = $update['channel_post']['chat'];
        $chatId = $chat['id'];
        
        if (!isset($foundChats[$chatId])) {
            $foundChats[$chatId] = [
                'id' => $chatId,
                'type' => $chat['type'],
                'title' => $chat['title'] ?? 'Unknown',
                'username' => $chat['username'] ?? null,
            ];
        }
    }
    
    // Dari chat_member update
    if (isset($update['chat_member']['chat'])) {
        $chat = $update['chat_member']['chat'];
        $chatId = $chat['id'];
        
        if (!isset($foundChats[$chatId])) {
            $foundChats[$chatId] = [
                'id' => $chatId,
                'type' => $chat['type'],
                'title' => $chat['title'] ?? 'Unknown',
                'username' => $chat['username'] ?? null,
            ];
        }
    }
    
    // Dari my_chat_member update
    if (isset($update['my_chat_member']['chat'])) {
        $chat = $update['my_chat_member']['chat'];
        $chatId = $chat['id'];
        
        if (!isset($foundChats[$chatId])) {
            $foundChats[$chatId] = [
                'id' => $chatId,
                'type' => $chat['type'],
                'title' => $chat['title'] ?? 'Unknown',
                'username' => $chat['username'] ?? null,
            ];
        }
    }
}

if (empty($foundChats)) {
    echo "⚠️  Tidak ada chat ditemukan dalam update.\n\n";
    exit(0);
}

echo "📋 DAFTAR CHAT YANG DITEMUKAN:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$index = 1;
foreach ($foundChats as $chat) {
    echo "{$index}. ";
    
    // Icon based on type
    if ($chat['type'] === 'channel') {
        echo "📢 CHANNEL";
    } elseif ($chat['type'] === 'supergroup') {
        echo "👥 SUPERGROUP";
    } elseif ($chat['type'] === 'group') {
        echo "👥 GROUP";
    } else {
        echo "💬 PRIVATE";
    }
    
    echo "\n";
    echo "   Nama: {$chat['title']}\n";
    echo "   Chat ID: {$chat['id']}\n";
    
    if ($chat['username']) {
        echo "   Username: @{$chat['username']}\n";
        echo "   Link: https://t.me/{$chat['username']}\n";
    }
    
    // Highlight if this is channel/group
    if (in_array($chat['type'], ['channel', 'supergroup', 'group'])) {
        echo "\n   🎯 GUNAKAN ID INI DI .ENV:\n";
        echo "   TELEGRAM_GROUP_ID={$chat['id']}\n";
    }
    
    echo "\n";
    $index++;
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

$currentGroupId = config('services.telegram.group_id');
echo "📌 KONFIGURASI SAAT INI:\n";
echo "   TELEGRAM_GROUP_ID={$currentGroupId}\n\n";

$matchFound = false;
foreach ($foundChats as $chat) {
    if ((string)$chat['id'] === (string)$currentGroupId) {
        echo "   ✅ ID ini cocok dengan: {$chat['title']}\n";
        $matchFound = true;
        break;
    }
}

if (!$matchFound && in_array($currentGroupId, [null, '', '0'])) {
    echo "   ⚠️  TELEGRAM_GROUP_ID belum diset!\n\n";
} elseif (!$matchFound) {
    echo "   ❌ ID ini TIDAK ditemukan dalam update!\n";
    echo "   Kemungkinan:\n";
    echo "   - Bot belum ditambahkan ke channel/grup dengan ID ini\n";
    echo "   - Channel/grup tidak aktif\n";
    echo "   - Belum ada aktivitas di channel/grup tersebut\n\n";
}

echo "LANGKAH SELANJUTNYA:\n";
echo "1. Salin Chat ID yang benar dari daftar di atas\n";
echo "2. Update file .env:\n";
echo "   TELEGRAM_GROUP_ID=<paste_chat_id_di_sini>\n";
echo "3. Pastikan bot menjadi ADMIN di channel/grup\n";
echo "4. Jalankan: php artisan telegram:set-webhook\n";
echo "5. Test: php check_bot_admin.php\n\n";
