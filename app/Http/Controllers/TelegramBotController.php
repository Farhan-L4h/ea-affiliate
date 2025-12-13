<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Order;
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

        // Default response
        $this->sendWelcomeMenu($chatId);
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
                $welcomeText = "🎉 <b>Selamat datang di EA Scalper Cepat MT5!</b>\n\n";
                $welcomeText .= "Anda datang melalui referral dari <b>{$affiliate->user->name}</b>\n\n";
                $welcomeText .= "Silahkan pilih menu di bawah ini untuk melanjutkan:";

                $this->sendWelcomeMenu($chatId, $welcomeText, $affiliateRef);
                return;
            }
        }

        // Default welcome
        $welcomeText = "🎉 <b>Selamat datang di bot manajemen pembelian kami!</b>\n\n";
        $welcomeText .= "Silakan pilih menu di bawah ini:";
        
        $this->sendWelcomeMenu($chatId, $welcomeText);
    }

    /**
     * Send welcome menu with inline buttons
     */
    protected function sendWelcomeMenu(int $chatId, ?string $text = null, ?string $affiliateRef = null): void
    {
        if (!$text) {
            $text = "🎉 <b>Selamat datang di bot manajemen pembelian kami!</b>\n\n";
            $text .= "Silakan pilih menu di bawah ini:";
        }

        $callbackData = $affiliateRef ? "buy_now:{$affiliateRef}" : "buy_now";

        $buttons = [
            [
                ['text' => '💰 Beli Sekarang', 'callback_data' => $callbackData],
            ],
            [
                ['text' => '📹 Video Backtest', 'callback_data' => 'video_backtest'],
                ['text' => '📊 Hasil Backtest', 'callback_data' => 'hasil_backtest'],
            ],
            [
                ['text' => '🎁 Uji Coba Gratis', 'callback_data' => 'free_trial'],
                ['text' => '💬 Tanya Langsung', 'callback_data' => 'ask_question'],
            ],
            [
                ['text' => '✨ Fitur-Fitur', 'callback_data' => 'features'],
                ['text' => '🛡️ Garansi Uang Kembali', 'callback_data' => 'money_back'],
            ],
        ];

        $this->telegram->sendInlineKeyboard($chatId, $text, $buttons);
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

            case 'video_backtest':
                $this->handleVideoBacktest($chatId);
                break;

            case 'hasil_backtest':
                $this->handleHasilBacktest($chatId);
                break;

            case 'free_trial':
                $this->handleFreeTrial($chatId);
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

            default:
                $this->sendWelcomeMenu($chatId);
                break;
        }
    }

    /**
     * Handle Buy Now button
     */
    protected function handleBuyNow(int $chatId, ?string $username, ?string $affiliateRef): void
    {
        // Product info
        $productName = 'EA Scalper Cepat MT5';
        $productPrice = 10000; // Rp 10.000

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
                $message .= "Pembayaran akan otomatis terverifikasi setelah transfer.";

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
     * Handle other menu items
     */
    protected function handleVideoBacktest(int $chatId): void
    {
        $message = "📹 <b>Video Backtest EA Scalper Cepat</b>\n\n";
        $message .= "Lihat hasil backtest EA kami di video berikut:\n";
        $message .= "🔗 [Link Video]\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleHasilBacktest(int $chatId): void
    {
        $message = "📊 <b>Hasil Backtest EA Scalper Cepat</b>\n\n";
        $message .= "Profit: 500%\n";
        $message .= "Drawdown: 15%\n";
        $message .= "Win Rate: 75%\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleFreeTrial(int $chatId): void
    {
        $message = "🎁 <b>Uji Coba Gratis</b>\n\n";
        $message .= "Dapatkan akses trial 7 hari!\n";
        $message .= "Hubungi admin untuk mendapatkan akses.\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleAskQuestion(int $chatId): void
    {
        $message = "💬 <b>Tanya Langsung</b>\n\n";
        $message .= "Silakan hubungi admin kami:\n";
        $message .= "📱 Telegram: @admin\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleFeatures(int $chatId): void
    {
        $message = "✨ <b>Fitur-Fitur EA Scalper Cepat</b>\n\n";
        $message .= "• Scalping otomatis\n";
        $message .= "• Risk management\n";
        $message .= "• Multi timeframe\n";
        $message .= "• Trailing stop\n";
        $message .= "• Dan masih banyak lagi!\n\n";
        $message .= "Untuk kembali ke menu utama, ketik /start";

        $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleMoneyBack(int $chatId): void
    {
        $message = "🛡️ <b>Garansi Uang Kembali</b>\n\n";
        $message .= "Kami memberikan garansi 30 hari uang kembali jika EA tidak sesuai harapan.\n\n";
        $message .= "Syarat dan ketentuan berlaku.\n\n";
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
