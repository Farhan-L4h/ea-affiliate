<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Http\Controllers\MootaWebhookController;
use Illuminate\Http\Request;

class TestMootaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moota:webhook-test {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Moota webhook dengan order ID tertentu (untuk sandbox testing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $this->info("Testing webhook for Order: {$orderId}");
        $this->newLine();

        // Find order
        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            $this->error("Order tidak ditemukan: {$orderId}");
            return 1;
        }

        // Display order info
        $this->info("Order Details:");
        $this->line("ID: {$order->order_id}");
        $this->line("Product: {$order->product}");
        $this->line("Base Amount: Rp " . number_format($order->base_amount, 0, ',', '.'));
        $this->line("Unique Code: {$order->unique_code}");
        $this->line("Total Amount: Rp " . number_format($order->total_amount, 0, ',', '.'));
        $this->line("Status: {$order->status}");
        $this->line("Affiliate Ref: " . ($order->affiliate_ref ?? '-'));
        $this->newLine();

        if ($order->status !== 'pending') {
            $this->warn("⚠️  Order status bukan 'pending', melainkan '{$order->status}'");
            if (!$this->confirm('Lanjutkan proses webhook?', false)) {
                return 0;
            }
        }

        // Simulate webhook data
        $webhookData = [
            'amount' => (string) $order->total_amount,
            'type' => 'CR',
            'description' => "Transfer dari customer untuk {$order->order_id}",
            'note' => $order->order_id,
            'tags' => [
                [
                    'name' => $order->order_id
                ]
            ],
        ];

        $this->info("Simulating webhook data:");
        $this->line(json_encode($webhookData, JSON_PRETTY_PRINT));
        $this->newLine();

        if (!$this->confirm('Process payment sekarang?')) {
            $this->info('Test dibatalkan.');
            return 0;
        }

        // Create request
        $request = Request::create('/webhook/moota', 'POST', $webhookData);

        // Call webhook handler
        try {
            $controller = app(MootaWebhookController::class);
            $response = $controller->handle($request);

            $this->newLine();
            if ($response->getStatusCode() === 200) {
                $this->info("✅ Webhook berhasil diproses!");
                
                // Reload order
                $order->refresh();
                
                $this->newLine();
                $this->info("Updated Order Status:");
                $this->line("Status: {$order->status}");
                $this->line("Paid At: " . ($order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '-'));
                
                // Check sale
                $sale = $order->sale;
                if ($sale) {
                    $this->newLine();
                    $this->info("✅ Sale record created:");
                    $this->line("Sale ID: {$sale->id}");
                    $this->line("Product: {$sale->product}");
                    $this->line("Sale Amount: Rp " . number_format($sale->sale_amount, 0, ',', '.'));
                    $this->line("Commission: Rp " . number_format($sale->commission_amount, 0, ',', '.') . " ({$sale->commission_percentage}%)");
                    $this->line("Sale Date: {$sale->sale_date}");
                } else {
                    $this->warn("⚠️  Sale record tidak ditemukan!");
                }

                $this->newLine();
                $this->info("Response: " . $response->getContent());
                
                return 0;
            } else {
                $this->error("❌ Webhook gagal!");
                $this->error("Status: " . $response->getStatusCode());
                $this->error("Response: " . $response->getContent());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error saat process webhook:");
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
