<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProvablyFairService
{
    /**
     * Generate a cryptographically secure server seed
     */
    public function generateServerSeed(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }

    /**
     * Generate a client seed (can be user-provided or generated)
     */
    public function generateClientSeed(): string
    {
        return bin2hex(random_bytes(16)); // 32 character hex string
    }

    /**
     * Get the next nonce for a user
     */
    public function getNextNonce(User $user): int
    {
        $cacheKey = "user_nonce_{$user->id}";
        $currentNonce = Cache::get($cacheKey, 0);
        $nextNonce = $currentNonce + 1;
        
        // Cache for 24 hours
        Cache::put($cacheKey, $nextNonce, now()->addDay());
        
        return $nextNonce;
    }

    /**
     * Generate a provably fair hash
     */
    public function generateHash(string $serverSeed, string $clientSeed, int $nonce): string
    {
        return hash('sha256', $serverSeed . ':' . $clientSeed . ':' . $nonce);
    }

    /**
     * Convert hash to decimal for randomization
     */
    public function hashToDecimal(string $hash, int $precision = 8): float
    {
        $hexSubstring = substr($hash, 0, $precision);
        $decimal = hexdec($hexSubstring);
        $maxValue = pow(16, $precision) - 1;
        
        return $decimal / $maxValue;
    }

    /**
     * Generate random number from hash
     */
    public function getRandomFromHash(string $serverSeed, string $clientSeed, int $nonce): float
    {
        $hash = $this->generateHash($serverSeed, $clientSeed, $nonce);
        return $this->hashToDecimal($hash);
    }

    /**
     * Verify a provably fair result
     */
    public function verifyResult(
        string $serverSeed,
        string $clientSeed,
        int $nonce,
        string $expectedHash
    ): bool {
        $generatedHash = $this->generateHash($serverSeed, $clientSeed, $nonce);
        return hash_equals($expectedHash, $generatedHash);
    }

    /**
     * Get random item from weighted array using provably fair
     */
    public function getRandomWeightedItem(
        array $items,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
        string $weightKey = 'weight'
    ): ?array {
        if (empty($items)) {
            return null;
        }

        $random = $this->getRandomFromHash($serverSeed, $clientSeed, $nonce);
        $totalWeight = array_sum(array_column($items, $weightKey));
        $target = $random * $totalWeight;

        $cumulative = 0;
        foreach ($items as $item) {
            $cumulative += $item[$weightKey];
            if ($target <= $cumulative) {
                return $item;
            }
        }

        // Fallback to first item if something goes wrong
        return $items[0];
    }

    /**
     * Generate multiple random numbers for batch operations
     */
    public function getBatchRandom(
        string $serverSeed,
        string $clientSeed,
        int $startNonce,
        int $count
    ): array {
        $results = [];
        
        for ($i = 0; $i < $count; $i++) {
            $nonce = $startNonce + $i;
            $results[] = [
                'nonce' => $nonce,
                'random' => $this->getRandomFromHash($serverSeed, $clientSeed, $nonce),
                'hash' => $this->generateHash($serverSeed, $clientSeed, $nonce)
            ];
        }
        
        return $results;
    }

    /**
     * Create provably fair data for case opening
     */
    public function createCaseOpeningData(User $user, string $clientSeed = null): array
    {
        $serverSeed = $this->generateServerSeed();
        $clientSeed = $clientSeed ?: $this->generateClientSeed();
        $nonce = $this->getNextNonce($user);
        $hash = $this->generateHash($serverSeed, $clientSeed, $nonce);
        
        return [
            'server_seed' => $serverSeed,
            'client_seed' => $clientSeed,
            'nonce' => $nonce,
            'hash' => $hash,
            'random' => $this->hashToDecimal($hash),
        ];
    }

    /**
     * Validate server seed format
     */
    public function validateServerSeed(string $serverSeed): bool
    {
        return preg_match('/^[a-f0-9]{64}$/i', $serverSeed) === 1;
    }

    /**
     * Validate client seed format
     */
    public function validateClientSeed(string $clientSeed): bool
    {
        return strlen($clientSeed) >= 1 && strlen($clientSeed) <= 128;
    }

    /**
     * Validate nonce
     */
    public function validateNonce(int $nonce): bool
    {
        return $nonce > 0 && $nonce <= 999999999;
    }

    /**
     * Get entropy information for transparency
     */
    public function getEntropyInfo(string $serverSeed, string $clientSeed, int $nonce): array
    {
        $hash = $this->generateHash($serverSeed, $clientSeed, $nonce);
        $decimal = $this->hashToDecimal($hash);
        
        return [
            'server_seed' => $serverSeed,
            'client_seed' => $clientSeed,
            'nonce' => $nonce,
            'combined_string' => $serverSeed . ':' . $clientSeed . ':' . $nonce,
            'sha256_hash' => $hash,
            'hex_substring' => substr($hash, 0, 8),
            'decimal_value' => hexdec(substr($hash, 0, 8)),
            'max_value' => pow(16, 8) - 1,
            'result' => $decimal,
            'percentage' => $decimal * 100,
        ];
    }

    /**
     * Log provably fair operation for audit
     */
    public function logOperation(
        string $operation,
        User $user,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
        array $additionalData = []
    ): void {
        Log::info('Provably Fair Operation', array_merge([
            'operation' => $operation,
            'user_id' => $user->id,
            'server_seed' => $serverSeed,
            'client_seed' => $clientSeed,
            'nonce' => $nonce,
            'hash' => $this->generateHash($serverSeed, $clientSeed, $nonce),
            'timestamp' => now()->toISOString(),
        ], $additionalData));
    }

    /**
     * Reset user nonce (for security purposes)
     */
    public function resetUserNonce(User $user): void
    {
        $cacheKey = "user_nonce_{$user->id}";
        Cache::forget($cacheKey);
        
        Log::info('User nonce reset', [
            'user_id' => $user->id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get user's current nonce without incrementing
     */
    public function getCurrentNonce(User $user): int
    {
        $cacheKey = "user_nonce_{$user->id}";
        return Cache::get($cacheKey, 0);
    }

    /**
     * Generate server seed for next round (used in some implementations)
     */
    public function generateNextServerSeed(string $currentServerSeed): string
    {
        return hash('sha256', $currentServerSeed . microtime(true));
    }

    /**
     * Batch verify multiple operations
     */
    public function batchVerify(array $operations): array
    {
        $results = [];
        
        foreach ($operations as $operation) {
            $isValid = $this->verifyResult(
                $operation['server_seed'],
                $operation['client_seed'],
                $operation['nonce'],
                $operation['expected_hash']
            );
            
            $results[] = array_merge($operation, ['is_valid' => $isValid]);
        }
        
        return $results;
    }

    /**
     * Calculate statistical distribution for analysis
     */
    public function calculateDistribution(array $results): array
    {
        $total = count($results);
        if ($total === 0) {
            return [];
        }

        $buckets = array_fill(0, 10, 0);
        
        foreach ($results as $result) {
            $bucket = min(9, floor($result * 10));
            $buckets[$bucket]++;
        }
        
        return [
            'total_samples' => $total,
            'buckets' => $buckets,
            'distribution' => array_map(function($count) use ($total) {
                return round(($count / $total) * 100, 2);
            }, $buckets),
            'expected_percentage' => 10.0, // Each bucket should have ~10%
            'chi_square' => $this->calculateChiSquare($buckets, $total / 10),
        ];
    }

    /**
     * Calculate Chi-Square statistic for randomness testing
     */
    private function calculateChiSquare(array $observed, float $expected): float
    {
        $chiSquare = 0;
        
        foreach ($observed as $obs) {
            $chiSquare += pow($obs - $expected, 2) / $expected;
        }
        
        return round($chiSquare, 4);
    }
}