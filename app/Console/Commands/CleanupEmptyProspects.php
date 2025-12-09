<?php

namespace App\Console\Commands;

use App\Models\ReferralTrack;
use Illuminate\Console\Command;

class CleanupEmptyProspects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prospects:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup empty prospect records that are older than 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = ReferralTrack::whereNull('prospect_telegram_id')
            ->whereNull('prospect_email')
            ->whereNull('prospect_phone')
            ->whereNull('prospect_telegram_username')
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $this->info("Deleted {$deleted} empty prospect records.");

        return 0;
    }
}
