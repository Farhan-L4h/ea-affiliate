<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class SetupTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram webhook URL';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        $webhookUrl = config('services.telegram.webhook_url');

        if (!$webhookUrl) {
            $this->error('TELEGRAM_WEBHOOK_URL not set in .env file');
            return 1;
        }

        $this->info('Setting webhook to: ' . $webhookUrl);

        // Tambahkan allowed_updates untuk menerima chat_member updates
        $allowedUpdates = ['message', 'callback_query', 'chat_member', 'my_chat_member'];
        $this->info('Allowed updates: ' . implode(', ', $allowedUpdates));

        $result = $telegram->setWebhook($webhookUrl, $allowedUpdates);

        if ($result['ok'] ?? false) {
            $this->info('✅ Webhook successfully set!');
            $this->info('Description: ' . ($result['description'] ?? 'N/A'));
            
            $this->newLine();
            $this->warn('⚠️  PENTING: Pastikan bot sudah menjadi ADMIN di channel/grup!');
            $this->warn('Channel ID: ' . config('services.telegram.group_id'));
            
            return 0;
        } else {
            $this->error('❌ Failed to set webhook');
            $this->error('Error: ' . ($result['description'] ?? 'Unknown error'));
            return 1;
        }
    }
}
