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

    /**
     * Send inline keyboard with buttons
     */
    public function sendInlineKeyboard(int|string $chatId, string $text, array $buttons): void
    {
        $keyboard = ['inline_keyboard' => $buttons];

        $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    /**
     * Send reply keyboard (persistent menu at bottom)
     */
    public function sendReplyKeyboard(int|string $chatId, string $text, array $buttons, bool $resize = true, bool $oneTime = false): void
    {
        $keyboard = [
            'keyboard' => $buttons,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
        ];

        $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    /**
     * Answer callback query
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): void
    {
        Http::post("{$this->apiUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    /**
     * Edit message text
     */
    public function editMessageText(int|string $chatId, int $messageId, string $text, array $extra = []): void
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $extra);

        Http::post("{$this->apiUrl}/editMessageText", $payload);
    }
}
