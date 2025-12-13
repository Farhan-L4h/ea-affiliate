<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Sale;
use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Services\MootaService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MootaWebhookController extends Controller
{
    protected MootaService $mootaService;
    protected TelegramService $telegramService;

    public function __construct(MootaService $mootaService, TelegramService $telegramService)
    {
        $this->mootaService = $mootaService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle Moota webhook callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Moota webhook received', $payload);

            // Moota sends data as array, get first element
            $mutation = is_array($payload) && isset($payload[0]) ? $payload[0] : $payload;

            // Verify webhook signature (optional but recommended)
            $signature = $request->header('X-Moota-Signature');
            if ($signature) {
                $isValid = $this->mootaService->verifyWebhookSignature($signature, $mutation);
                if (!$isValid) {
                    Log::warning('Invalid Moota webhook signature');
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }
            
            // Extract order ID from note/description
            $orderId = $this->extractOrderId($mutation);
            
            if (!$orderId) {
                Log::warning('Order ID not found in Moota webhook', $mutation);
                return response()->json(['message' => 'Order ID not found'], 200);
            }

            // Find order
            $order = Order::where('order_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if (!$order) {
                Log::warning('Order not found or already processed', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found or already processed'], 200);
            }

            // Verify amount
            $mutationAmount = (float) $mutation['amount'];
            if ($mutationAmount != $order->total_amount) {
                Log::warning('Amount mismatch', [
                    'order_id' => $orderId,
                    'expected' => $order->total_amount,
                    'received' => $mutationAmount,
                ]);
                return response()->json(['message' => 'Amount mismatch'], 200);
            }

            // Check if mutation is credit (incoming)
            if (($mutation['type'] ?? 'CR') !== 'CR') {
                Log::warning('Not a credit transaction', $mutation);
                return response()->json(['message' => 'Not a credit transaction'], 200);
            }

            // Process payment
            $this->processPayment($order);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Moota webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Extract order ID from mutation data
     */
    protected function extractOrderId(array $mutation): ?string
    {
        // Check in description
        $description = $mutation['description'] ?? '';
        
        // Try to find ORDER-xxxxx pattern
        if (preg_match('/ORDER-[A-Z0-9]+/i', $description, $matches)) {
            return strtoupper($matches[0]);
        }

        // Check in note
        $note = $mutation['note'] ?? '';
        if (preg_match('/ORDER-[A-Z0-9]+/i', $note, $matches)) {
            return strtoupper($matches[0]);
        }

        // Check in items array (untuk Moota Sandbox format)
        $items = $mutation['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $item) {
                // Check in item name
                if (preg_match('/ORDER-[A-Z0-9]+/i', $item['name'] ?? '', $matches)) {
                    Log::info('Order ID found in items[name]', ['order_id' => strtoupper($matches[0])]);
                    return strtoupper($matches[0]);
                }
                // Check in item description
                if (preg_match('/ORDER-[A-Z0-9]+/i', $item['description'] ?? '', $matches)) {
                    Log::info('Order ID found in items[description]', ['order_id' => strtoupper($matches[0])]);
                    return strtoupper($matches[0]);
                }
            }
        }

        // Check in tags/taggings
        $tags = $mutation['tags'] ?? $mutation['taggings'] ?? [];
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tagName = is_array($tag) ? ($tag['name'] ?? '') : $tag;
                if (preg_match('/ORDER-[A-Z0-9]+/i', $tagName, $matches)) {
                    Log::info('Order ID found in tags', ['order_id' => strtoupper($matches[0])]);
                    return strtoupper($matches[0]);
                }
            }
        }

        // Check in contacts name (some payment gateways put it here)
        if (isset($mutation['contacts']['name'])) {
            $contactName = $mutation['contacts']['name'];
            if (preg_match('/ORDER-[A-Z0-9]+/i', $contactName, $matches)) {
                Log::info('Order ID found in contacts[name]', ['order_id' => strtoupper($matches[0])]);
                return strtoupper($matches[0]);
            }
        }

        Log::warning('Order ID not found in mutation', [
            'description' => $description,
            'note' => $note,
            'items_count' => count($items),
            'tags_count' => count($tags),
        ]);

        return null;
    }

    /**
     * Process payment and commission distribution
     */
    protected function processPayment(Order $order): void
    {
        // Mark order as paid
        $order->markAsPaid();

        // Get affiliate if exists
        $affiliate = null;
        if ($order->affiliate_ref) {
            $affiliate = Affiliate::where('ref_code', $order->affiliate_ref)->first();
        }

        // Calculate commission
        $commissionRate = $affiliate ? ($affiliate->commission_rate ?? 20) : 0;
        $commissionAmount = ($order->base_amount * $commissionRate) / 100;

        // Create sale record
        $sale = Sale::create([
            'order_id' => $order->id,
            'affiliate_id' => $affiliate ? $affiliate->id : null,
            'product' => $order->product,
            'sale_amount' => $order->base_amount,
            'commission_percentage' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'sale_date' => now(),
        ]);

        // Process commission if affiliate exists
        if ($affiliate) {
            $this->processCommission($order, $sale, $affiliate, $commissionAmount);
        }

        // Update referral track status to purchased
        if ($order->telegram_chat_id) {
            ReferralTrack::where('prospect_telegram_id', $order->telegram_chat_id)
                ->update(['status' => 'purchased']);
        }

        // Notify customer via Telegram
        if ($order->telegram_chat_id) {
            $this->notifyCustomer($order);
        }

        Log::info('Payment processed successfully', [
            'order_id' => $order->order_id,
            'sale_id' => $sale->id,
        ]);
    }

    /**
     * Process affiliate commission
     */
    protected function processCommission(Order $order, Sale $sale, Affiliate $affiliate, float $commissionAmount): void
    {
        // Create payout record
        AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'sale_id' => $sale->id,
            'amount' => $commissionAmount,
            'status' => 'pending',
        ]);

        // Update affiliate stats
        $affiliate->increment('total_sales');
        $affiliate->increment('total_commission', $commissionAmount);

        Log::info('Commission processed', [
            'affiliate_id' => $affiliate->id,
            'commission' => $commissionAmount,
        ]);
    }

    /**
     * Notify customer via Telegram
     */
    protected function notifyCustomer(Order $order): void
    {
        $message = "✅ Pembayaran Berhasil!\n\n";
        $message .= "Order ID: {$order->order_id}\n";
        $message .= "Produk: {$order->product}\n";
        $message .= "Total: Rp " . number_format((float)$order->total_amount, 0, ',', '.') . "\n\n";
        $message .= "Terima kasih atas pembelian Anda! 🎉\n\n";
        $message .= "📺 Tutorial Cara Pasang EA:\n";
        $message .= "🔗 https://www.youtube.com/watch?v=iNbzsabpRoE\n\n";
        $message .= "Jika ada kesulitan, hubungi admin @alwaysrighttt\n\n";
        $message .= "Selamat menggunakan EA Scalper Max Pro! 🚀";

        try {
            $this->telegramService->sendMessage($order->telegram_chat_id, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
