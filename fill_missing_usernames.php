<?php
/**
 * Script untuk mengisi username yang kosong dengan fetch dari Telegram API
 * 
 * Jalankan dengan: php fill_missing_usernames.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReferralTrack;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

$botToken = config('services.telegram.bot_token');

if (!$botToken) {
    echo "❌ Bot token tidak ditemukan di config!\n";
    exit(1);
}

echo "🔍 Mencari data prospek tanpa username...\n\n";

// Ambil data yang punya telegram_id tapi username kosong
$prospects = ReferralTrack::whereNotNull('prospect_telegram_id')
    ->where(function($q) {
        $q->whereNull('prospect_telegram_username')
          ->orWhere('prospect_telegram_username', '');
    })
    ->orderBy('created_at', 'desc')
    ->get();

$total = $prospects->count();
echo "📊 Ditemukan {$total} prospek tanpa username\n\n";

if ($total === 0) {
    echo "✅ Semua prospek sudah punya username!\n";
    exit(0);
}

$updated = 0;
$failed = 0;
$noUsername = 0;

foreach ($prospects as $index => $prospect) {
    $num = $index + 1;
    echo "[{$num}/{$total}] Telegram ID: {$prospect->prospect_telegram_id} ... ";
    
    try {
        // Gunakan getChatMember untuk mendapatkan info user
        // Kita perlu chat_id (bisa pakai grup atau bot chat)
        $groupId = config('services.telegram.group_id');
        
        $response = Http::get("https://api.telegram.org/bot{$botToken}/getChatMember", [
            'chat_id' => $groupId,
            'user_id' => $prospect->prospect_telegram_id,
        ]);
        
        $result = $response->json();
        
        if ($result['ok'] ?? false) {
            $user = $result['result']['user'] ?? null;
            
            if ($user) {
                $username = $user['username'] ?? null;
                $firstName = $user['first_name'] ?? '';
                $lastName = $user['last_name'] ?? '';
                $name = trim($firstName . ' ' . $lastName);
                
                $updateData = [];
                
                if ($username) {
                    $updateData['prospect_telegram_username'] = $username;
                }
                
                if ($name && empty($prospect->prospect_name)) {
                    $updateData['prospect_name'] = $name;
                }
                
                if (!empty($updateData)) {
                    $prospect->update($updateData);
                    
                    if ($username) {
                        echo "✅ Updated: @{$username}";
                        if ($name) echo " ({$name})";
                        echo "\n";
                        $updated++;
                    } else {
                        echo "⚠️  No username (Name: {$name})\n";
                        $noUsername++;
                    }
                } else {
                    echo "⚠️  No new data\n";
                    $noUsername++;
                }
            } else {
                echo "❌ User data not found\n";
                $failed++;
            }
        } else {
            $error = $result['description'] ?? 'Unknown error';
            echo "❌ API Error: {$error}\n";
            $failed++;
        }
        
        // Rate limiting: tunggu 0.5 detik per request
        usleep(500000);
        
    } catch (\Exception $e) {
        echo "❌ Exception: {$e->getMessage()}\n";
        $failed++;
    }
}

echo "\n";
echo "═══════════════════════════════════════════\n";
echo "📈 HASIL AKHIR:\n";
echo "───────────────────────────────────────────\n";
echo "✅ Berhasil di-update : {$updated}\n";
echo "⚠️  Tidak punya username: {$noUsername}\n";
echo "❌ Gagal/Error        : {$failed}\n";
echo "📊 Total              : {$total}\n";
echo "═══════════════════════════════════════════\n";
