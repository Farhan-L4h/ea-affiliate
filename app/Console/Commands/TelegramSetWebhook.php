<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';
    protected $description = 'Set Telegram bot webhook';

    public function handle(TelegramService $telegram)
    {
        $url = config('services.telegram.webhook_url');
        
        // Tambahkan allowed_updates untuk menerima chat_member updates
        $allowedUpdates = ['message', 'callback_query', 'chat_member', 'my_chat_member'];
        
        $this->info('Setting webhook to: ' . $url);
        $this->info('Allowed updates: ' . implode(', ', $allowedUpdates));
        
        $res = $telegram->setWebhook($url, $allowedUpdates);

        if ($res['ok'] ?? false) {
            $this->info('✅ Webhook berhasil diset!');
            $this->info('Description: ' . ($res['description'] ?? 'N/A'));
        } else {
            $this->error('❌ Gagal set webhook');
            $this->error('Error: ' . ($res['description'] ?? 'Unknown error'));
        }
        
        $this->newLine();
        $this->warn('⚠️  PENTING: Pastikan bot sudah menjadi ADMIN di channel/grup!');
        $this->warn('Channel ID: ' . config('services.telegram.group_id'));
        
        return $res['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
