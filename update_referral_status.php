<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ReferralTrack;
use App\Models\Order;

echo "Updating referral track statuses...\n";
echo "==================================\n\n";

// Get all referral tracks
$tracks = ReferralTrack::all();

foreach ($tracks as $track) {
    // Check if this telegram user has any paid orders
    $paidOrder = Order::where('telegram_chat_id', $track->prospect_telegram_id)
        ->where('status', 'paid')
        ->first();
    
    if ($paidOrder) {
        $track->status = 'purchased';
        $track->save();
        echo "✅ Updated {$track->prospect_telegram_username} to 'purchased'\n";
    } else {
        // Check if has pending orders
        $pendingOrder = Order::where('telegram_chat_id', $track->prospect_telegram_id)
            ->where('status', 'pending')
            ->first();
        
        if ($pendingOrder) {
            $track->status = 'order_created';
            $track->save();
            echo "⏳ Updated {$track->prospect_telegram_username} to 'order_created'\n";
        } else {
            // Keep as is or update to started
            if ($track->status === 'clicked') {
                $track->status = 'started';
                $track->save();
                echo "🔵 Updated {$track->prospect_telegram_username} to 'started'\n";
            }
        }
    }
}

echo "\n✅ Done!\n";
