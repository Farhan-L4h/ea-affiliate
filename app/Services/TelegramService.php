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

    /**
     * Send document/file to chat
     */
    public function sendDocument(int|string $chatId, string $filePath, string $caption = ''): void
    {
        try {
            $response = Http::attach(
                'document',
                file_get_contents($filePath),
                basename($filePath)
            )->post("{$this->apiUrl}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                \Log::error('Failed to send document', [
                    'file' => basename($filePath),
                    'response' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending document', [
                'file' => basename($filePath),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send multiple documents to chat
     */
    public function sendDocuments(int|string $chatId, array $filePaths, string $caption = ''): void
    {
        foreach ($filePaths as $index => $filePath) {
            if (file_exists($filePath)) {
                // Send caption only with first file
                $fileCaption = ($index === 0) ? $caption : '';
                $this->sendDocument($chatId, $filePath, $fileCaption);
                
                // Small delay to avoid rate limiting
                usleep(300000); // 0.3 seconds
            } else {
                \Log::warning('File not found for sending', ['file' => $filePath]);
            }
        }
    }
}
