<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Sale;
use App\Models\Affiliate;
use App\Models\ReferralTrack;
use App\Services\MootaService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:check {order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and verify payment from Moota for pending orders';

    protected MootaService $mootaService;
    protected TelegramService $telegramService;

    public function __construct(MootaService $mootaService, TelegramService $telegramService)
    {
        parent::__construct();
        $this->mootaService = $mootaService;
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        if ($orderId) {
            // Check specific order
            $this->checkSpecificOrder($orderId);
        } else {
            // Check all pending orders
            $this->checkAllPendingOrders();
        }
    }

    protected function checkSpecificOrder(string $orderId)
    {
        $this->info("Checking payment for order: {$orderId}");

        $order = Order::where('order_id', $orderId)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            $this->error("Order not found or already processed: {$orderId}");
            return;
        }

        $this->processOrder($order);
    }

    protected function checkAllPendingOrders()
    {
        $this->info("Checking all pending orders...");

        $orders = Order::where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(7)) // Only check last 7 days
            ->get();

        if ($orders->isEmpty()) {
            $this->info("No pending orders found.");
            return;
        }

        $this->info("Found {$orders->count()} pending orders.");

        foreach ($orders as $order) {
            $this->processOrder($order);
        }
    }

    protected function processOrder(Order $order)
    {
        $this->line("Processing: {$order->order_id} - Rp " . number_format($order->total_amount, 0, ',', '.'));

        // Get mutations from Moota
        $mutations = $this->getMutationsForOrder($order);

        if (empty($mutations)) {
            $this->warn("  ⚠ No matching mutation found in Moota");
            return;
        }

        $this->info("  ✓ Found " . count($mutations) . " matching mutation(s)");

        // Check each mutation
        foreach ($mutations as $mutation) {
            $amount = (float) ($mutation['amount'] ?? 0);
            
            // Must match exact amount
            if ($amount == $order->total_amount) {
                $this->info("  ✓ Amount matched: Rp " . number_format($amount, 0, ',', '.'));
                
                // Additional info about how it was matched
                $description = $mutation['description'] ?? '';
                $note = $mutation['note'] ?? '';
                if (str_contains(strtoupper($description), $order->order_id) || str_contains(strtoupper($note), $order->order_id)) {
                    $this->info("  ✓ Matched by: Order ID in description/note");
                } else {
                    $this->info("  ✓ Matched by: Unique amount (Rp " . number_format($amount, 0, ',', '.') . ")");
                }
                
                // Process payment
                $this->processPayment($order);
                
                $this->info("  ✓ Payment processed successfully!");
                return;
            } else {
                $this->warn("  ⚠ Amount mismatch - Expected: {$order->total_amount}, Got: {$amount}");
            }
        }

        $this->warn("  ⚠ Mutations found but none matched the exact amount");
    }

    protected function getMutationsForOrder(Order $order): array
    {
        try {
            // Search by description/note containing order ID OR by exact amount match
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.moota.token'),
                'Accept' => 'application/json',
            ])->get(config('services.moota.api_url') . '/mutation', [
                'type' => 'CR', // Credit only (incoming)
                'per_page' => 100,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $allMutations = $response->json('data', []);
            
            // Filter mutations by Order ID OR exact amount
            $matchingMutations = [];
            foreach ($allMutations as $mutation) {
                $mutationAmount = (float) ($mutation['amount'] ?? 0);
                $description = strtoupper($mutation['description'] ?? '');
                $note = strtoupper($mutation['note'] ?? '');
                
                // Method 1: Check if Order ID exists in description or note
                if (str_contains($description, $order->order_id) || str_contains($note, $order->order_id)) {
                    $matchingMutations[] = $mutation;
                    continue;
                }
                
                // Method 2: Match by exact amount (including unique code)
                if ($mutationAmount == $order->total_amount) {
                    $matchingMutations[] = $mutation;
                    continue;
                }
                
                // Method 3: Check items array (untuk Moota Sandbox)
                $items = $mutation['items'] ?? [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $itemName = strtoupper($item['name'] ?? '');
                        $itemDesc = strtoupper($item['description'] ?? '');
                        
                        if (str_contains($itemName, $order->order_id) || str_contains($itemDesc, $order->order_id)) {
                            $matchingMutations[] = $mutation;
                            break;
                        }
                    }
                }
            }

            return $matchingMutations;
        } catch (\Exception $e) {
            $this->error("  ✗ Error fetching mutations: " . $e->getMessage());
            return [];
        }
    }

    protected function processPayment(Order $order): void
    {
        // Mark order as paid
        $order->status = 'paid';
        $order->paid_at = now();
        $order->save();

        // Get affiliate if exists
        $affiliate = null;
        if ($order->affiliate_ref) {
            $affiliate = Affiliate::where('ref_code', $order->affiliate_ref)->first();
        }

        // Calculate commission
        $commissionRate = $affiliate ? ($affiliate->commission_rate ?? 30) : 0;
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
        if ($affiliate && $commissionAmount > 0) {
            $affiliate->total_commission += $commissionAmount;
            $affiliate->total_sales += $order->base_amount;
            $affiliate->save();

            $this->info("  ✓ Commission Rp " . number_format($commissionAmount, 0, ',', '.') . " added to affiliate {$affiliate->ref_code}");
        }

        // Update referral track status to purchased
        if ($order->telegram_chat_id) {
            ReferralTrack::where('prospect_telegram_id', $order->telegram_chat_id)
                ->update(['status' => 'purchased']);
        }

        // Notify customer via Telegram
        if ($order->telegram_chat_id) {
            try {
                $message = "✅ Pembayaran Berhasil!\n\n";
                $message .= "Order ID: {$order->order_id}\n";
                $message .= "Produk: {$order->product}\n";
                $message .= "Total: Rp " . number_format((float)$order->total_amount, 0, ',', '.') . "\n\n";
                $message .= "Terima kasih atas pembelian Anda! 🎉\n\n";
                $message .= "📺 Tutorial Cara Pasang EA:\n";
                $message .= "🔗 https://www.youtube.com/watch?v=iNbzsabpRoE\n\n";
                $message .= "Jika ada kesulitan, hubungi admin @Desa_trading\n\n";
                $message .= "Selamat menggunakan EA Scalper Max Pro! 🚀";
                
                $this->telegramService->sendMessage($order->telegram_chat_id, $message);
                $this->info("  ✓ Telegram notification sent to customer");

                // Send EA Scalper files
                $scalperPath = resource_path('scalper');
                $files = [
                    $scalperPath . '/SMP v3.1.ex5',
                    $scalperPath . '/high v3.set',
                    $scalperPath . '/medium v3.set',
                    $scalperPath . '/low v3.set',
                    $scalperPath . '/Server.txt',
                ];

                $this->telegramService->sendDocuments($order->telegram_chat_id, $files);
                $this->info("  ✓ EA Scalper files sent to customer");

            } catch (\Exception $e) {
                $this->warn("  ⚠ Failed to send Telegram notification: " . $e->getMessage());
            }
        }

        Log::info('Payment processed via command', [
            'order_id' => $order->order_id,
            'sale_id' => $sale->id,
            'commission' => $commissionAmount,
        ]);
    }
}
