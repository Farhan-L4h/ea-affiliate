<?php

/**
 * Script untuk mengecek apakah bot Telegram sudah menjadi admin di channel/grup
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$botToken = config('services.telegram.bot_token');
$groupId = config('services.telegram.group_id');

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         CEK STATUS BOT DI CHANNEL/GRUP TELEGRAM                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Bot Token: " . substr($botToken, 0, 15) . "...\n";
echo "Group/Channel ID: {$groupId}\n\n";

// Get bot info
echo "🤖 Mengambil info bot...\n";
$botInfo = Http::get("https://api.telegram.org/bot{$botToken}/getMe")->json();

if ($botInfo['ok'] ?? false) {
    $bot = $botInfo['result'];
    echo "   Bot Username: @{$bot['username']}\n";
    echo "   Bot Name: {$bot['first_name']}\n";
    echo "   Bot ID: {$bot['id']}\n\n";
} else {
    echo "   ❌ Gagal mengambil info bot\n\n";
    exit(1);
}

// Get chat info
echo "💬 Mengambil info chat/channel...\n";
$chatInfo = Http::get("https://api.telegram.org/bot{$botToken}/getChat", [
    'chat_id' => $groupId
])->json();

if ($chatInfo['ok'] ?? false) {
    $chat = $chatInfo['result'];
    echo "   Chat Title: {$chat['title']}\n";
    echo "   Chat Type: {$chat['type']}\n";
    if (isset($chat['username'])) {
        echo "   Chat Username: @{$chat['username']}\n";
    }
    echo "\n";
} else {
    echo "   ❌ Gagal mengambil info chat\n";
    echo "   Error: " . ($chatInfo['description'] ?? 'Unknown') . "\n\n";
    echo "   ⚠️  Bot mungkin belum ditambahkan ke channel/grup ini!\n\n";
    exit(1);
}

// Get bot member status
echo "🔍 Mengecek status bot di channel/grup...\n";
$memberInfo = Http::get("https://api.telegram.org/bot{$botToken}/getChatMember", [
    'chat_id' => $groupId,
    'user_id' => $bot['id']
])->json();

if ($memberInfo['ok'] ?? false) {
    $member = $memberInfo['result'];
    $status = $member['status'];
    
    echo "   Status: {$status}\n";
    
    if ($status === 'administrator') {
        echo "   ✅ Bot adalah ADMINISTRATOR\n";
        
        // Check permissions
        if (isset($member['can_promote_members'])) {
            echo "\n   📋 Permission yang dimiliki:\n";
            echo "   - Can be edited: " . ($member['can_be_edited'] ? '✓' : '✗') . "\n";
            echo "   - Can manage chat: " . ($member['can_manage_chat'] ? '✓' : '✗') . "\n";
            echo "   - Can delete messages: " . ($member['can_delete_messages'] ? '✓' : '✗') . "\n";
            echo "   - Can manage video chats: " . ($member['can_manage_video_chats'] ? '✓' : '✗') . "\n";
            echo "   - Can restrict members: " . ($member['can_restrict_members'] ? '✓' : '✗') . "\n";
            echo "   - Can promote members: " . ($member['can_promote_members'] ? '✓' : '✗') . "\n";
            echo "   - Can change info: " . ($member['can_change_info'] ? '✓' : '✗') . "\n";
            echo "   - Can invite users: " . ($member['can_invite_users'] ? '✓' : '✗') . "\n";
            echo "   - Can post messages: " . ($member['can_post_messages'] ?? false ? '✓' : '✗') . "\n";
            echo "   - Can edit messages: " . ($member['can_edit_messages'] ?? false ? '✓' : '✗') . "\n";
        }
        
        echo "\n   ✅ BOT SUDAH SIAP MENDETEKSI JOIN MEMBER!\n";
    } elseif ($status === 'creator') {
        echo "   ✅ Bot adalah CREATOR (Owner)\n";
        echo "\n   ✅ BOT SUDAH SIAP MENDETEKSI JOIN MEMBER!\n";
    } else {
        echo "   ❌ Bot bukan administrator!\n";
        echo "   Status saat ini: {$status}\n\n";
        echo "   ⚠️  SOLUSI:\n";
        echo "   1. Buka channel/grup Telegram\n";
        echo "   2. Klik nama channel/grup → Administrators\n";
        echo "   3. Klik 'Add Admin'\n";
        echo "   4. Cari dan tambahkan @{$bot['username']}\n";
        echo "   5. Berikan minimal permission untuk 'Add members'\n\n";
    }
} else {
    echo "   ❌ Gagal mengecek status bot\n";
    echo "   Error: " . ($memberInfo['description'] ?? 'Unknown') . "\n\n";
}

// Check webhook status
echo "\n🌐 Mengecek status webhook...\n";
$webhookInfo = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo")->json();

if ($webhookInfo['ok'] ?? false) {
    $webhook = $webhookInfo['result'];
    
    echo "   URL: " . ($webhook['url'] ?: '(tidak diset)') . "\n";
    echo "   Has custom certificate: " . ($webhook['has_custom_certificate'] ? 'Ya' : 'Tidak') . "\n";
    echo "   Pending update count: " . ($webhook['pending_update_count'] ?? 0) . "\n";
    
    if (isset($webhook['last_error_date'])) {
        echo "   ⚠️  Last error: " . $webhook['last_error_message'] . "\n";
        echo "   Last error date: " . date('Y-m-d H:i:s', $webhook['last_error_date']) . "\n";
    }
    
    if (isset($webhook['allowed_updates'])) {
        echo "   Allowed updates: " . implode(', ', $webhook['allowed_updates']) . "\n";
        
        if (in_array('chat_member', $webhook['allowed_updates'])) {
            echo "   ✅ chat_member sudah termasuk dalam allowed_updates\n";
        } else {
            echo "   ❌ chat_member TIDAK ada dalam allowed_updates!\n";
            echo "\n   ⚠️  SOLUSI: Jalankan command berikut:\n";
            echo "   php artisan telegram:set-webhook\n\n";
        }
    } else {
        echo "   ⚠️  Allowed updates tidak diset (akan terima semua updates)\n";
    }
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    LANGKAH TROUBLESHOOTING                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Jika status masih 'Klik Link' padahal sudah join:\n\n";
echo "1. ✓ Pastikan bot adalah ADMIN di channel/grup\n";
echo "2. ✓ Set ulang webhook dengan command:\n";
echo "      php artisan telegram:set-webhook\n\n";
echo "3. ✓ Test dengan user baru:\n";
echo "      - Klik link affiliate baru\n";
echo "      - Join channel\n";
echo "      - Cek status di dashboard\n\n";
echo "4. ✓ Cek log webhook di storage/logs/laravel.log\n\n";

echo "\n";
