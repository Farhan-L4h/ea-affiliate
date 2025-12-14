<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\ReferralTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram update received', ['update' => $update]);

            // Pesan biasa: /start AFRATFYI
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            // Perubahan member di grup / channel
            if (isset($update['chat_member'])) {
                $this->handleChatMember($update['chat_member']);
            }

            if (isset($update['my_chat_member'])) {
                $this->handleChatMember($update['my_chat_member']);
            }

            return response()->json(['ok' => true]);
            
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Still return 200 to prevent Telegram from retrying
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Handle /start <REFCODE> dari user (klik link affiliate)
     */
    protected function handleMessage(array $message): void
    {
        $chat = $message['chat'] ?? [];
        $text = $message['text'] ?? '';

        // Bot cuma respon di chat private
        if (($chat['type'] ?? null) !== 'private') {
            return;
        }

        // Harus mulai dengan /start
        if (strpos($text, '/start') !== 0) {
            return;
        }

        // Ambil ref code setelah /start
        $parts = explode(' ', trim($text), 2);
        $ref   = strtoupper($parts[1] ?? '');

        if ($ref === '') {
            return;
        }

        $from = $message['from'] ?? [];
        $telegramId       = $from['id'] ?? null;
        $telegramUsername = $from['username'] ?? null;
        $name             = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));

        if (! $telegramId) {
            return;
        }

        // Cari lead berdasar telegram_id + ref_code
        $lead = ReferralTrack::where('prospect_telegram_id', $telegramId)
            ->where('ref_code', $ref)
            ->first();

        if ($lead) {
            // Update info kalau sebelumnya masih kosong
            $lead->update([
                'prospect_name'              => $name ?: $lead->prospect_name,
                'prospect_telegram_username' => $telegramUsername ?: $lead->prospect_telegram_username,
            ]);
        } else {
            // Cari jejak klik kosong (dari middleware) yang belum punya telegram_id
            $recentEmptyLead = ReferralTrack::where('ref_code', $ref)
                ->whereNull('prospect_telegram_id')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($recentEmptyLead) {
                $recentEmptyLead->update([
                    'prospect_telegram_id'       => $telegramId,
                    'prospect_name'              => $name ?: null,
                    'prospect_telegram_username' => $telegramUsername,
                ]);

                $lead = $recentEmptyLead;
            } else {
                // Bener-bener baru
                $lead = ReferralTrack::create([
                    'ref_code'                   => $ref,
                    'prospect_telegram_id'       => $telegramId,
                    'prospect_name'              => $name ?: null,
                    'prospect_telegram_username' => $telegramUsername,
                    'status'                     => 'clicked',
                ]);
            }
        }

        // Jangan nurunin status kalau sudah lebih tinggi
        if (! in_array($lead->status, ['joined_channel', 'purchased'], true)) {
            $lead->update(['status' => 'clicked']);
        }

        // Kirim pesan welcome + link channel
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

    /**
     * Handle ketika user JOIN ke channel / grup edukasi
     */
    protected function handleChatMember(array $chatMember): void
    {
        try {
            $chat = $chatMember['chat'] ?? null;
            $new  = $chatMember['new_chat_member'] ?? null;

            if (! $chat || ! $new) {
                Log::debug('Chat member update incomplete', ['chat_member' => $chatMember]);
                return;
            }

            // Hanya track untuk channel/grup target kita
            $targetGroupId = (int) config('services.telegram.group_id');
            if ((int) ($chat['id'] ?? 0) !== $targetGroupId) {
                Log::debug('Chat member update for different group', [
                    'received_id' => $chat['id'] ?? 0,
                    'target_id' => $targetGroupId
                ]);
                return;
            }

            $user   = $new['user'] ?? null;
            $status = $new['status'] ?? null;

            // Status harus member / admin / owner
            if (! $user || ! in_array($status, ['member', 'administrator', 'creator'], true)) {
                Log::debug('Chat member status not tracked', [
                    'status' => $status,
                    'user_id' => $user['id'] ?? null
                ]);
                return;
            }

            $telegramId = $user['id'];
            $telegramUsername = $user['username'] ?? null;
            $firstName = $user['first_name'] ?? '';
            $lastName = $user['last_name'] ?? '';
            $name = trim($firstName . ' ' . $lastName);

            // Ambil lead terakhir dari orang ini
            $lead = ReferralTrack::where('prospect_telegram_id', $telegramId)
                ->latest()
                ->first();

            if (! $lead) {
                Log::info('Join channel tanpa lead', [
                    'telegram_id' => $telegramId,
                    'username' => $telegramUsername,
                    'first_name' => $firstName
                ]);
                return;
            }

            // UPDATE: Selalu update username & name jika belum ada
            $updateData = [];
            if (empty($lead->prospect_telegram_username) && $telegramUsername) {
                $updateData['prospect_telegram_username'] = $telegramUsername;
            }
            if (empty($lead->prospect_name) && $name) {
                $updateData['prospect_name'] = $name;
            }
            if (!empty($updateData)) {
                $lead->update($updateData);
                Log::info('Updated prospect info on join', [
                    'telegram_id' => $telegramId,
                    'updates' => $updateData
                ]);
            }

            // Kalau sudah purchased / joined_channel jangan diutak-atik
            if (! in_array($lead->status, ['joined_channel', 'purchased'], true)) {
                $lead->update([
                    'status' => 'joined_channel',
                ]);

                Log::info('Status updated to joined_channel', [
                    'telegram_id' => $telegramId,
                    'prospect_username' => $lead->prospect_telegram_username,
                    'ref_code' => $lead->ref_code
                ]);

                // Tambah total join sekali aja per lead
                try {
                    Affiliate::where('ref_code', $lead->ref_code)->increment('total_joins');
                } catch (\Exception $e) {
                    Log::warning('Failed to increment total_joins', [
                        'ref_code' => $lead->ref_code,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::debug('Lead status already advanced', [
                    'telegram_id' => $telegramId,
                    'current_status' => $lead->status
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in handleChatMember', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'chat_member' => $chatMember
            ]);
            throw $e;
        }
    }
}
