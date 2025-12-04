<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token  = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function setWebhook(string $url): array
    {
        $res = Http::post("{$this->apiUrl}/setWebhook", [
            'url' => $url,
        ]);

        return $res->json();
    }

    public function sendMessage(int|string $chatId, string $text, array $extra = []): void
    {
        $payload = array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $extra);

        Http::post("{$this->apiUrl}/sendMessage", $payload);
    }
}
