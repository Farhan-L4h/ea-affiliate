<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\ReferralTrack;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', $update);

            // Handle callback query (button clicks)
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
                return response()->json(['ok' => true]);
            }

            // Handle messages
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
                return response()->json(['ok' => true]);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false], 500);
        }
    }

    /**
     * Handle incoming messages
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? null;

        // Command: /start
        if (str_starts_with($text, '/start')) {
            $this->handleStartCommand($chatId, $text, $username);
            return;
        }

        // Command: /help
        if ($text === '/help') {
            $this->sendHelpMessage($chatId);
            return;
        }

        // Command: /status
        if (str_starts_with($text, '/status')) {
            $this->handleStatusCommand($chatId, $text);
            return;
        }

        // Handle reply keyboard buttons
        switch ($text) {
            case '💰 Beli Sekarang':
                $this->handleBuyNow($chatId, $username, null);
                break;

            case '💬 Tanya Langsung':
                $this->handleAskQuestion($chatId);
                break;

            case '✨ Fitur-Fitur':
                $this->handleFeatures($chatId);
                break;

            case '🛡️ Garansi Uang Kembali':
                $this->handleMoneyBack($chatId);
                break;

            case '📢 Gabung Channel':
                $this->handleJoinChannel($chatId);
                break;

            case '🎯 Lifetime - Rp 1.300.000':
                $this->handleBuyLifetime($chatId, $username, null);
                break;

            case '⏰ Sewa - Rp 600.000':
                $this->handleBuyRent($chatId, $username, null);
                break;

            case '« Kembali':
                $this->sendWelcomeMenu($chatId);
                break;

            default:
                // Default response
                $this->sendWelcomeMenu($chatId);
                break;
        }
    }

    /**
     * Handle /start command
     */
    protected function handleStartCommand(int $chatId, string $text, ?string $username): void
    {
        // Check if referral code exists
        $parts = explode(' ', $text);
        $affiliateRef = $parts[1] ?? null;

        if ($affiliateRef) {
            // Validate affiliate code
            $affiliate = Affiliate::with('user')->where('ref_code', $affiliateRef)->first();

            if ($affiliate) {
                // Save or update referral tracking
                ReferralTrack::updateOrCreate(
                    ['prospect_telegram_id' => (string)$chatId],
                    [
                        'prospect_telegram_username' => $username,
                        'ref_code' => $affiliateRef,
                        'status' => 'started',
                    ]
                );

                $welcomeText = "🎉 <b>Selamat datang di EA Scalper Max Pro!</b>\n\n";
                $welcomeText .= "Anda datang melalui referral dari <b>{$affiliate->user->name}</b>\n\n";
                $welcomeText .= "Silahkan pilih menu di bawah ini untuk melanjutkan:";

                $this->sendWelcomeMenu($chatId, $welcomeText, $affiliateRef);
                return;
            }
        }

        // Default welcome
        $welcomeText = "🎉 <b>Selamat datang di EA Scalper Max Pro!</b>\n\n";
        $welcomeText .= "Silakan pilih menu di bawah ini:";
        
        $this->sendWelcomeMenu($chatId, $welcomeText);
    }

    /**
     * Send welcome menu with reply keyboard
     */
    protected function sendWelcomeMenu(int $chatId, ?string $text = null, ?string $affiliateRef = null): void
    {
        if (!$text) {
            $text = "🎉 <b>Selamat datang di EA Scalper Max Pro!</b>\n\n";
            $text .= "Silakan pilih menu di bawah ini:";
        }

        // Use reply keyboard (menu at bottom)
        $buttons = [
            [
                ['text' => '💰 Beli Sekarang'],
                ['text' => '💬 Tanya Langsung'],
            ],
            [
                ['text' => '✨ Fitur-Fitur'],
                ['text' => '🛡️ Garansi Uang Kembali'],
            ],
            [
                ['text' => '📢 Gabung Channel'],
            ],
        ];

        $this->telegram->sendReplyKeyboard($chatId, $text, $buttons);
    }

    /**
     * Handle callback query (button clicks)
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'];
        $username = $callbackQuery['from']['username'] ?? null;

        // Parse callback data
        $parts = explode(':', $data);
        $action = $parts[0];
        $param = $parts[1] ?? null;

        // Answer callback query
        $this->telegram->answerCallbackQuery($callbackId);

        switch ($action) {
            case 'buy_now':
                $this->handleBuyNow($chatId, $username, $param);
                break;

            case 'buy_lifetime':
                $this->handleBuyLifetime($chatId, $username, $param);
                break;

            case 'buy_rent':
                $this->handleBuyRent($chatId, $username, $param);
                break;

            case 'ask_question':
                $this->handleAskQuestion($chatId);
                break;

            case 'features':
                $this->handleFeatures($chatId);
                break;

            case 'money_back':
                $this->handleMoneyBack($chatId);
                break;

            case 'tutorial':
                $this->handleTutorial($chatId);
                break;

            case 'back_to_menu':
                $this->sendWelcomeMenu($chatId);
                break;

            default:
                $this->sendWelcomeMenu($chatId);
                break;
        }
    }

    /**
     * Handle Buy Now button - Show package options
     */
    protected function handleBuyNow(int $chatId, ?string $username, ?string $affiliateRef): void
    {
        // Get referral tracking if not provided
        if (!$affiliateRef) {
            $track = ReferralTrack::where('prospect_telegram_id', (string)$chatId)->first();
            if ($track) {
                $affiliateRef = $track->ref_code;
            }
        }

        $message = "💰 <b>Pilih Paket EA Scalper Max Pro:</b>\n\n";
        $message .= "🔹 <b>LIFETIME</b>\n";
        $message .= "   Harga: Rp 1.300.000\n";
        $message .= "   Full Support & Update Selamanya\n\n";
        $message .= "🔹 <b>SEWA</b>\n";
        $message .= "   Harga: Rp 600.000\n";
        $message .= "   Support & Update Selama Masa Sewa\n\n";
        $message .= "Silakan pilih paket yang Anda inginkan:";

        $buttons = [
            [
                ['text' => '🎯 Lifetime - Rp 1.300.000'],
            ],
            [
                ['text' => '⏰ Sewa - Rp 600.000'],
            ],
            [
                ['text' => '« Kembali'],
            ],
        ];

        $this->telegram->sendReplyKeyboard($chatId, $message, $buttons);
    }

    /**
     * Handle Buy Lifetime
     */
    protected function handleBuyLifetime(int $chatId, ?string $username, ?string $affiliateRef): void
    {
        // Get referral tracking if not provided
        if (!$affiliateRef) {
            $track = ReferralTrack::where('prospect_telegram_id', (string)$chatId)->first();
            if ($track) {
                $affiliateRef = $track->ref_code;
            }
        }

        $productName = 'EA Scalper Max Pro - LIFETIME';
        $productPrice = 10000; // Rp 10.000

        $this->processPayment($chatId, $username, $affiliateRef, $productName, $productPrice);
    }

    /**
     * Handle Buy Rent
     */
    protected function handleBuyRent(int $chatId, ?string $username, ?string $affiliateRef): void
    {
        // Get referral tracking if not provided
        if (!$affiliateRef) {
            $track = ReferralTrack::where('prospect_telegram_id', (string)$chatId)->first();
            if ($track) {
                $affiliateRef = $track->ref_code;
            }
        }

        $productName = 'EA Scalper Max Pro - SEWA';
        $productPrice = 6000; // Rp 6.000

        $this->processPayment($chatId, $username, $affiliateRef, $productName, $productPrice);
    }

    /**
     * Process payment creation
     */
    protected function processPayment(int $chatId, ?string $username, ?string $affiliateRef, string $productName, int $productPrice): void
    {

        try {
            // Create payment directly via controller (avoid ngrok timeout)
            $paymentController = app(\App\Http\Controllers\PaymentController::class);
            
            $request = new \Illuminate\Http\Request([
                'telegram_chat_id' => $chatId,
                'telegram_username' => $username,
                'product' => $productName,
                'amount' => $productPrice,
                'affiliate_ref' => $affiliateRef,
            ]);
            
            $response = $paymentController->createFromTelegram($request);
            $responseData = $response->getData(true);

            if ($responseData['success'] ?? false) {
                $data = $responseData['data'];

                $message = "🎉 <b>Silakan selesaikan pembayaran Anda melalui link berikut:</b>\n\n";
                $message .= "🔗 {$data['payment_url']}\n\n";
                $message .= "💳 <b>Informasi Pembayaran:</b>\n";
                $message .= "Bank: {$data['payment_info']['bank']}\n";
                $message .= "No. Rekening: <code>{$data['payment_info']['account_number']}</code>\n";
                $message .= "Atas Nama: {$data['payment_info']['account_name']}\n\n";
                $message .= "💰 <b>Total Transfer:</b> Rp " . number_format($data['total_amount'], 0, ',', '.') . "\n";
                $message .= "🔢 <b>Kode Unik:</b> {$data['unique_code']}\n\n";
                $message .= "⏰ <b>Batas Waktu:</b> " . date('d M Y H:i', strtotime($data['expired_at'])) . "\n\n";
                $message .= "📝 <b>Order ID:</b> <code>{$data['order_id']}</code>\n\n";
                $message .= "Pembayaran akan otomatis terverifikasi setelah transfer.\n\n";
                $message .= "Setelah pembayaran lunas, Anda akan menerima link tutorial cara pasang EA.";

                $this->telegram->sendMessage($chatId, $message);
            } else {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ Maaf, terjadi kesalahan saat membuat pembayaran. Silakan coba lagi."
                );
            }
        } catch (\Exception $e) {
            Log::error('Buy now error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->telegram->sendMessage(
                $chatId,
                "❌ Maaf, terjadi kesalahan. Silakan coba lagi nanti."
            );
        }
    }

    /**
     * Handle Tutorial
     */
    protected function handleTutorial(int $chatId): void
    {
        $message = "📺 <b>Cara Pasang EA Scalper Max Pro</b>\n\n";
        $message .= "Tonton video tutorial lengkap cara install dan setup EA:\n\n";
        $message .= "🔗 https://www.youtube.com/watch?v=iNbzsabpRoE\n\n";
        $message .= "Jika ada kesulitan, hubungi admin @alwaysrighttt\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleAskQuestion(int $chatId): void
    {
        $message = "💬 <b>Tanya Langsung</b>\n\n";
        $message .= "Silakan hubungi admin kami untuk konsultasi atau pertanyaan:\n\n";
        $message .= "📱 Telegram: @alwaysrighttt\n\n";
        $message .= "Admin siap membantu Anda!\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleFeatures(int $chatId): void
    {
        $message = "✨ <b>Kenapa EA Ini Aman dan Bisa Profit Konsisten?</b>\n\n";
        $message .= "1️⃣ Menerapkan Sistem Low - Medium - High\n";
        $message .= "2️⃣ Sistem Grid Step dan Hedging Maksimum Layer\n";
        $message .= "3️⃣ Lock & Unlock Hedging SL+ BE\n";
        $message .= "4️⃣ TP SL Harian\n";
        $message .= "5️⃣ Draw Down Kontrol\n";
        $message .= "6️⃣ News Filter\n";
        $message .= "7️⃣ Bulk Close (Khusus V2)\n";
        $message .= "8️⃣ Potensial Profit Harian 2-10%\n";
        $message .= "9️⃣ VIP Support Full Lifetime Update Sesuai Kondisi Pasar\n\n";
        $message .= "💡 <i>EA dirancang untuk profit konsisten dengan risk management yang ketat!</i>\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleJoinChannel(int $chatId): void
    {
        $message = "📢 <b>Gabung Channel Scalper Max Pro</b>\n\n";
        $message .= "Join grup edukasi kami untuk mendapatkan:\n\n";
        $message .= "✅ Update EA terbaru\n";
        $message .= "✅ Tips & trik trading\n";
        $message .= "✅ Analisa market harian\n";
        $message .= "✅ Setting EA optimal\n";
        $message .= "✅ Support dari tim & komunitas\n\n";
        $message .= "👉 Klik link di bawah untuk bergabung:\n";
        $message .= "https://t.me/scalpermaxproai\n\n";
        $message .= "Kontak admin: @Desa_trading";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleMoneyBack(int $chatId): void
    {
        $message = "🛡️ <b>Garansi 100% Jika MC Uang Kembali</b>\n\n";
        $message .= "<b>*Syarat Ketentuan Berlaku:</b>\n";
        $message .= "1️⃣ Garansi Berlaku untuk Full Version\n";
        $message .= "2️⃣ Memakai Set dan EA Update Terbaru\n";
        $message .= "3️⃣ Garansi Berlaku Sampai Modal Beli Robot dan Modal Trading Kembali\n";
        $message .= "4️⃣ Garansi Kembali untuk Uang Pembelian EA\n";
        $message .= "5️⃣ Full Support Selamanya di Grup VIP Member\n";
        $message .= "6️⃣ Modal Optimal 600$\n";
        $message .= "7️⃣ Akses Akun dan VPS Kami yang Settingkan\n\n";
        $message .= "💰 <i>Garansi ini menunjukkan kami serius dengan kualitas EA!</i>\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleStatusCommand(int $chatId, string $text): void
    {
        $parts = explode(' ', $text);
        $orderId = $parts[1] ?? null;

        if (!$orderId) {
            $this->telegram->sendMessage(
                $chatId,
                "Gunakan format: /status ORDER-xxxxx"
            );
            return;
        }

        $order = Order::where('order_id', $orderId)
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (!$order) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ Order tidak ditemukan."
            );
            return;
        }

        $statusEmoji = [
            'pending' => '⏳',
            'paid' => '✅',
            'expired' => '❌',
            'cancelled' => '🚫',
        ];

        $message = "📋 <b>Status Pembayaran</b>\n\n";
        $message .= "Order ID: <code>{$order->order_id}</code>\n";
        $message .= "Status: {$statusEmoji[$order->status]} " . ucfirst($order->status) . "\n";
        $message .= "Total: Rp " . number_format((float)$order->total_amount, 0, ',', '.') . "\n\n";

        if ($order->status === 'paid') {
            $message .= "✅ Pembayaran berhasil!\n";
            $message .= "Dibayar pada: " . $order->paid_at->format('d M Y H:i');
        } elseif ($order->status === 'pending') {
            $message .= "⏳ Menunggu pembayaran...\n";
            $message .= "Batas waktu: " . $order->expired_at->format('d M Y H:i');
        }

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function sendHelpMessage(int $chatId): void
    {
        $message = "📖 <b>Bantuan</b>\n\n";
        $message .= "Perintah yang tersedia:\n";
        $message .= "/start - Mulai bot\n";
        $message .= "/status ORDER-xxxxx - Cek status pembayaran\n";
        $message .= "/help - Bantuan\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }
}
