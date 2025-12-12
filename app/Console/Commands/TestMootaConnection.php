<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MootaService;

class TestMootaConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moota:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Moota API connection and display bank accounts';

    /**
     * Execute the console command.
     */
    public function handle(MootaService $moota)
    {
        $this->info('Testing Moota API connection...');
        $this->newLine();

        $accounts = $moota->getBankAccounts();

        if ($accounts) {
            $this->info('✅ Connection successful!');
            $this->newLine();
            $this->info('Bank Accounts:');

            foreach ($accounts as $account) {
                $this->line('');
                $this->line('ID: ' . ($account['bank_id'] ?? 'N/A'));
                $this->line('Bank: ' . ($account['bank_type'] ?? 'N/A'));
                $this->line('Account Name: ' . ($account['atas_nama'] ?? 'N/A'));
                $this->line('Account Number: ' . ($account['account_number'] ?? 'N/A'));
                $this->line('Balance: Rp ' . number_format($account['balance'] ?? 0, 0, ',', '.'));
                $this->line('---');
            }

            $this->newLine();
            $this->info('💡 Copy the Bank ID and add to your .env:');
            $this->info('MOOTA_BANK_ACCOUNT_ID=' . ($accounts[0]['bank_id'] ?? 'xxx'));

            return 0;
        } else {
            $this->error('❌ Failed to connect to Moota API');
            $this->error('Please check your MOOTA_TOKEN in .env file');
            return 1;
        }
    }
}
