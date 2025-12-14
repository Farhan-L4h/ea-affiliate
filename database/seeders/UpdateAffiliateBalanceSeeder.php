<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Affiliate;
use App\Models\Sale;

class UpdateAffiliateBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update affiliate balance based on sales
        $affiliates = Affiliate::all();

        foreach ($affiliates as $affiliate) {
            // Get total commission from sales
            $totalCommission = Sale::where('affiliate_id', $affiliate->id)
                ->sum('commission_amount');

            // Get total withdrawn
            $totalWithdrawn = $affiliate->payouts()
                ->where('status', 'paid')
                ->sum('amount');

            // Update balances
            $affiliate->update([
                'total_commission' => $totalCommission,
                'available_balance' => $totalCommission - $totalWithdrawn,
                'withdrawn_balance' => $totalWithdrawn,
            ]);

            $this->command->info("Updated affiliate {$affiliate->ref_code}: Commission={$totalCommission}, Available={$affiliate->available_balance}");
        }

        $this->command->info('Affiliate balances updated successfully!');
    }
}
