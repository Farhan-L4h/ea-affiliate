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
            Log::info('Moota webhook received', $request->all());

            // Verify webhook signature (optional but recommended)
            $signature = $request->header('X-Moota-Signature');
            if ($signature) {
                $isValid = $this->mootaService->verifyWebhookSignature($signature, $request->all());
                if (!$isValid) {
                    Log::warning('Invalid Moota webhook signature');
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            // Get mutation data
            $mutation = $request->all();
            
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

        // Check in tag
        $tags = $mutation['tags'] ?? [];
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if (preg_match('/ORDER-[A-Z0-9]+/i', $tag['name'] ?? '', $matches)) {
                    return strtoupper($matches[0]);
                }
            }
        }

        return null;
    }

    /**
     * Process payment and commission distribution
     */
    protected function processPayment(Order $order): void
    {
        // Mark order as paid
        $order->markAsPaid();

        // Create sale record
        $sale = Sale::create([
            'user_id' => $order->user_id,
            'affiliate_ref' => $order->affiliate_ref,
            'product' => $order->product,
            'amount' => $order->base_amount,
            'status' => 'completed',
        ]);

        // Link order to sale
        $order->update(['sale_id' => $sale->id]);

        // Process commission if affiliate exists
        if ($order->affiliate_ref) {
            $this->processCommission($order, $sale);
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
    protected function processCommission(Order $order, Sale $sale): void
    {
        $affiliate = Affiliate::where('ref_code', $order->affiliate_ref)
            ->first();

        if (!$affiliate) {
            Log::warning('Affiliate not found', ['ref' => $order->affiliate_ref]);
            return;
        }

        // Calculate commission (example: 20% of base amount)
        $commissionRate = $affiliate->commission_rate ?? 20; // Default 20%
        $commissionAmount = ($order->base_amount * $commissionRate) / 100;

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
        $message = "✅ <b>Pembayaran Berhasil!</b>\n\n";
        $message .= "Order ID: <code>{$order->order_id}</code>\n";
        $message .= "Produk: {$order->product}\n";
        $message .= "Total: Rp " . number_format((float)$order->total_amount, 0, ',', '.') . "\n\n";
        $message .= "Terima kasih atas pembelian Anda!\n";
        $message .= "Link download dan akses produk akan segera dikirimkan.";

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
