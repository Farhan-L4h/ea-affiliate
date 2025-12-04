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
        $res = $telegram->setWebhook($url);

        $this->info('Response: ' . json_encode($res));
        return self::SUCCESS;
    }
}
