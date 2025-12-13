<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ReferralTrack;

echo "Current Referral Track Statuses:\n";
echo "================================\n\n";

$tracks = ReferralTrack::all();

foreach ($tracks as $track) {
    echo "{$track->prospect_telegram_username} - {$track->status} - {$track->ref_code}\n";
}

echo "\n";
