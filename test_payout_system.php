<?php
/**
 * Script untuk testing sistem pencairan komisi
 * Jalankan dengan: php test_payout_system.php
 */

require __DIR__.'/vendor/autoload.php';

use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\User;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Testing Payout System ===\n\n";

// 1. Check affiliates with balance
echo "1. Checking affiliates with available balance:\n";
$affiliates = Affiliate::where('available_balance', '>', 0)->get();
foreach ($affiliates as $affiliate) {
    echo "   - {$affiliate->ref_code}: Rp " . number_format($affiliate->available_balance, 0, ',', '.') . "\n";
    echo "     Bank Info: " . ($affiliate->hasBankInfo() ? "✓ Complete" : "✗ Incomplete") . "\n";
    if ($affiliate->hasBankInfo()) {
        echo "     Bank: {$affiliate->bank_name} - {$affiliate->account_number} ({$affiliate->account_holder_name})\n";
    }
}

echo "\n2. Checking payout requests:\n";
$payouts = AffiliatePayout::with('affiliate')->where('request_type', 'manual')->get();
if ($payouts->count() > 0) {
    foreach ($payouts as $payout) {
        echo "   - #{$payout->id} [{$payout->affiliate->ref_code}] ";
        echo "Rp " . number_format($payout->amount, 0, ',', '.') . " - ";
        echo "Status: {$payout->status}\n";
    }
} else {
    echo "   No payout requests yet.\n";
}

echo "\n3. System Status:\n";
echo "   - Total Affiliates: " . Affiliate::count() . "\n";
echo "   - Affiliates with bank info: " . Affiliate::whereNotNull('bank_name')->count() . "\n";
echo "   - Pending Payouts: " . AffiliatePayout::where('status', 'pending')->where('request_type', 'manual')->count() . "\n";
echo "   - Approved Payouts: " . AffiliatePayout::where('status', 'approved')->where('request_type', 'manual')->count() . "\n";
echo "   - Paid Payouts: " . AffiliatePayout::where('status', 'paid')->where('request_type', 'manual')->count() . "\n";

echo "\n4. URLs to access:\n";
echo "   - Affiliate Payout: http://127.0.0.1:8000/payout\n";
echo "   - Admin Payouts: http://127.0.0.1:8000/admin/payouts\n";

echo "\n=== Test Complete ===\n\n";
