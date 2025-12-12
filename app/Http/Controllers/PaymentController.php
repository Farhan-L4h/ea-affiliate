<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReferralTrack;
use App\Services\MootaService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MootaService $mootaService;
    protected TelegramService $telegramService;

    public function __construct(MootaService $mootaService, TelegramService $telegramService)
    {
        $this->mootaService = $mootaService;
        $this->telegramService = $telegramService;
    }

    /**
     * Create payment from Telegram bot
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFromTelegram(Request $request)
    {
        $request->validate([
            'telegram_chat_id' => 'required',
            'telegram_username' => 'nullable|string',
            'product' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'affiliate_ref' => 'nullable|string',
        ]);

        try {
            // Generate unique code
            $paymentData = $this->mootaService->generateUniqueCode($request->amount);

            // Generate order ID
            $orderId = Order::generateOrderId();

            // Set expiration (24 hours)
            $expiredAt = now()->addHours(24);

            // Payment info
            $paymentInfo = [
                'bank' => 'BCA',
                'account_number' => '0111502977',
                'account_name' => 'Udin Nurwachid',
            ];

            // Create order
            $order = Order::create([
                'order_id' => $orderId,
                'telegram_chat_id' => $request->telegram_chat_id,
                'telegram_username' => $request->telegram_username,
                'affiliate_ref' => $request->affiliate_ref,
                'product' => $request->product,
                'base_amount' => $request->amount,
                'unique_code' => $paymentData['unique_code'],
                'total_amount' => $paymentData['amount'],
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'payment_info' => $paymentInfo,
                'expired_at' => $expiredAt,
            ]);

            // Create tagging in Moota
            $bankAccountId = config('services.moota.bank_account_id');
            if ($bankAccountId) {
                $tagging = $this->mootaService->createTagging(
                    $orderId,
                    $paymentData['amount'],
                    $bankAccountId
                );

                if ($tagging) {
                    $order->update(['moota_tagging_id' => $tagging['data']['tagging_id'] ?? null]);
                }
            }

            // Track referral if exists
            if ($request->affiliate_ref) {
                ReferralTrack::create([
                    'affiliate_ref' => $request->affiliate_ref,
                    'visitor_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'clicked_at' => now(),
                ]);
            }

            // Generate payment URL
            $paymentUrl = route('payment.show', ['orderId' => $orderId]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'payment_url' => $paymentUrl,
                    'total_amount' => $paymentData['amount'],
                    'unique_code' => $paymentData['unique_code'],
                    'payment_info' => $paymentInfo,
                    'expired_at' => $expiredAt->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Show payment page
     * 
     * @param string $orderId
     * @return \Illuminate\View\View
     */
    public function show(string $orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        // Check if expired
        if ($order->isExpired() && $order->status === 'pending') {
            $order->markAsExpired();
        }

        return view('payment.show', compact('order'));
    }

    /**
     * Check payment status
     * 
     * @param string $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(string $orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        // Check if expired
        if ($order->isExpired() && $order->status === 'pending') {
            $order->markAsExpired();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->order_id,
                'status' => $order->status,
                'paid_at' => $order->paid_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
