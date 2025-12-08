<?php
namespace App\Http\Controllers;
// app/Http/Controllers/TelegramWebhookController.php
use App\Models\Affiliate;
use App\Models\ReferralTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Telegram update', $request->all());

        $update = $request->all();

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        if (isset($update['chat_member'])) {
            $this->handleChatMember($update['chat_member']);
        }

        if (isset($update['my_chat_member'])) {
            $this->handleChatMember($update['my_chat_member']);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleMessage(array $message): void
    {
        $chat = $message['chat'] ?? [];
        $text = $message['text'] ?? '';

        if (($chat['type'] ?? null) !== 'private') {
            return;
        }

        if (strpos($text, '/start') !== 0) {
            return;
        }

        $parts = explode(' ', trim($text), 2);
        $ref   = strtoupper($parts[1] ?? '');

        if ($ref === '') {
            return;
        }

        $from = $message['from'] ?? [];
        $telegramId       = $from['id'] ?? null;
        $telegramUsername = $from['username'] ?? null;
        $name             = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));

        // kalau belum ada => buat dengan status clicked
        $lead = ReferralTrack::firstOrCreate(
            [
                'prospect_telegram_id' => $telegramId,
                'ref_code'             => $ref,
            ],
            [
                'prospect_name'              => $name ?: null,
                'prospect_telegram_username' => $telegramUsername,
                'status'                     => 'clicked',
            ]
        );

        // kalau nanti status-nya sudah purchased / joined_channel, jangan diturunin lagi
        if (! in_array($lead->status, ['joined_channel', 'purchased'])) {
            $lead->update(['status' => 'clicked']);
        }

        // kirim pesan welcome + link group
        $botToken = config('services.telegram.bot_token');
        $chatId   = $chat['id'];

        $welcomeText = "Halo {$name} 👋\n\n"
            . "Kamu datang lewat link affiliate: <b>{$ref}</b>.\n"
            . "Kalau nanti jadi beli EA, komisi otomatis masuk ke sponsor pertama ini. 😉\n\n"
            . "Join grup edukasi di sini:\n"
            . "https://t.me/scalpermaxproai";

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $welcomeText,
            'parse_mode' => 'HTML',
        ]);
    }

        protected function handleChatMember(array $chatMember): void
    {
        $chat = $chatMember['chat'] ?? null;
        $new  = $chatMember['new_chat_member'] ?? null;

        if (! $chat || ! $new) {
            return;
        }

        $targetGroupId = (int) config('services.telegram.group_id');
        if ((int) ($chat['id'] ?? 0) !== $targetGroupId) {
            return;
        }

        $user   = $new['user'] ?? null;
        $status = $new['status'] ?? null;

        if (! $user || ! in_array($status, ['member', 'administrator'], true)) {
            return;
        }

        $telegramId = $user['id'];

        $lead = ReferralTrack::where('prospect_telegram_id', $telegramId)
            ->latest()
            ->first();

        if (! $lead) {
            return;
        }

        // kalau sudah purchased, jangan turunin status
        if ($lead->status !== 'joined_channel' && $lead->status !== 'purchased') {
            $lead->update([
                'status' => 'joined_channel',
            ]);

            // naikin total join channel cuma sekali
            Affiliate::where('ref_code', $lead->ref_code)->increment('total_joins');
        }
    }
}

