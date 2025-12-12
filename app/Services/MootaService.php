<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MootaService
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.moota.api_url', 'https://app.moota.co/api/v2');
        $this->token  = config('services.moota.token');
    }

    /**
     * Generate unique payment code
     * 
     * @param float $baseAmount
     * @return array ['amount' => final_amount, 'unique_code' => code]
     */
    public function generateUniqueCode(float $baseAmount): array
    {
        // Generate kode unik 3 digit (001-999)
        $uniqueCode = mt_rand(1, 999);
        $finalAmount = $baseAmount + $uniqueCode;

        return [
            'amount' => $finalAmount,
            'unique_code' => $uniqueCode,
        ];
    }

    /**
     * Create tagging for payment tracking
     * 
     * @param string $orderId
     * @param float $amount
     * @param string $bankAccountId
     * @return array|null
     */
    public function createTagging(string $orderId, float $amount, string $bankAccountId): ?array
    {
        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->put("{$this->apiUrl}/tagging/store", [
                'name' => $orderId,
                'amount' => $amount,
                'bank_account_id' => $bankAccountId,
                'description' => "Payment for order {$orderId}",
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Moota tagging failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Moota tagging exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get bank accounts
     * 
     * @return array|null
     */
    public function getBankAccounts(): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/bank");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Moota get bank accounts exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check mutation by unique code
     * 
     * @param string $orderId
     * @return array|null
     */
    public function checkMutation(string $orderId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/mutation", [
                'tag' => $orderId,
            ]);

            if ($response->successful()) {
                $data = $response->json('data');
                if (!empty($data)) {
                    return $data[0]; // Return first matching mutation
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Moota check mutation exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Verify webhook signature
     * 
     * @param string $signature
     * @param array $payload
     * @return bool
     */
    public function verifyWebhookSignature(string $signature, array $payload): bool
    {
        $secret = config('services.moota.webhook_secret');
        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $secret);
        
        return hash_equals($calculatedSignature, $signature);
    }
}
