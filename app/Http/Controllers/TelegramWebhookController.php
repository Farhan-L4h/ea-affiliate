<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\ReferralTrack;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
    ) {}

    public function handle(Request $request)
    {
        $update  = $request->all();
        $message = $update['message'] ?? null;

        if (! $message) {
            return response()->json(['ok' => true]);
        }

        $chatId     = $message['chat']['id'] ?? null;
        $from       = $message['from'] ?? [];
        $telegramId = $from['id'] ?? null;
        $username   = $from['username'] ?? null;
        $firstName  = $from['first_name'] ?? null;
        $lastName   = $from['last_name'] ?? null;

        $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

        $text = trim($message['text'] ?? '');

        if (str_starts_with($text, '/start')) {
            $parts   = explode(' ', $text);
            $refCode = $parts[1] ?? null;

            if ($refCode) {
                $refCode = strtoupper($refCode);

                // 1. cari track by telegram_id dulu
                $track = ReferralTrack::where('prospect_telegram_id', $telegramId)->first();

                // 2. kalau belum ada, coba ambil track klik-link (ref sama, belum ada telegram id)
                if (! $track) {
                    $track = ReferralTrack::where('ref_code', $refCode)
                        ->whereNull('prospect_telegram_id')
                        ->orderByDesc('id')
                        ->first();
                }

                if (! $track) {
                    // 3. bener-bener baru, bikin baru
                    $track = ReferralTrack::create([
                        'ref_code'                   => $refCode,
                        'prospect_telegram_id'       => (string) $telegramId,
                        'prospect_telegram_username' => $username,
                        'prospect_name'              => $fullName ?: null,
                        'status'                     => 'joined_bot',
                    ]);

                    Affiliate::where('ref_code', $refCode)->increment('total_joins');
                } else {
                    // update data + status
                    $track->ref_code                   = $track->ref_code ?: $refCode;
                    $track->prospect_telegram_id       = (string) $telegramId;
                    $track->prospect_telegram_username = $username ?? $track->prospect_telegram_username;
                    $track->prospect_name              = $fullName ?: $track->prospect_name;

                    if ($track->status !== 'purchased') {
                        $track->status = 'joined_bot';
                    }

                    $track->save();

                    Affiliate::where('ref_code', $refCode)->increment('total_joins');
                }

                $displayName = $username ?? ($fullName ?: 'Trader');

                $this->telegram->sendMessage(
                    $chatId,
                    "Halo <b>{$displayName}</b> 👋\n\n" .
                    "Kamu datang lewat link affiliate: <b>{$refCode}</b>.\n" .
                    "Kalau nanti jadi beli EA, komisi otomatis masuk ke sponsor pertama ini. 😉"
                );
            }

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }
}
