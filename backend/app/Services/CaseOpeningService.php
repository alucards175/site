<?php

namespace App\Services;

use App\Models\User;
use App\Models\CaseModel;
use App\Models\CaseOpening;
use App\Models\UserInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaseOpeningService
{
    protected ProvablyFairService $provablyFairService;

    public function __construct(ProvablyFairService $provablyFairService)
    {
        $this->provablyFairService = $provablyFairService;
    }

    /**
     * Open a case for a user
     */
    public function openCase(User $user, CaseModel $case, string $clientSeed): CaseOpening
    {
        // Validate that user can open the case
        if (!$case->canBeOpenedBy($user)) {
            throw new \Exception('User cannot open this case');
        }

        // Generate server seed and nonce
        $serverSeed = $this->provablyFairService->generateServerSeed();
        $nonce = $this->provablyFairService->getNextNonce($user);

        // Get random item using provably fair algorithm
        $item = $case->getRandomItem($serverSeed, $clientSeed, $nonce);
        
        if (!$item) {
            throw new \Exception('No items available in this case');
        }

        // Generate float value for the item
        $floatValue = $item->generateFloat();

        // Create the hash for provably fair verification
        $hash = hash('sha256', $serverSeed . ':' . $clientSeed . ':' . $nonce);

        // Deduct balance from user
        if (!$user->deductBalance($case->price)) {
            throw new \Exception('Insufficient balance');
        }

        // Create case opening record
        $opening = CaseOpening::create([
            'user_id' => $user->id,
            'case_id' => $case->id,
            'item_id' => $item->id,
            'case_price' => $case->price,
            'item_value' => $item->price,
            'server_seed' => $serverSeed,
            'client_seed' => $clientSeed,
            'nonce' => $nonce,
            'hash' => $hash,
            'float_value' => $floatValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Add item to user's inventory
        UserInventory::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'case_opening_id' => $opening->id,
            'float_value' => $floatValue,
            'acquired_at' => now(),
            'status' => 'active',
        ]);

        // Update user activity
        $user->updateLastActivity();

        // Log the opening for audit
        Log::info('Case opened', [
            'user_id' => $user->id,
            'case_id' => $case->id,
            'item_id' => $item->id,
            'opening_id' => $opening->id,
            'profit' => $opening->profit,
        ]);

        return $opening;
    }

    /**
     * Get case opening statistics for a user
     */
    public function getUserStatistics(User $user): array
    {
        $openings = $user->caseOpenings();

        return [
            'total_openings' => $openings->count(),
            'total_spent' => $openings->sum('case_price'),
            'total_won' => $openings->sum('item_value'),
            'total_profit' => $openings->sum('profit'),
            'profitable_openings' => $openings->where('profit', '>', 0)->count(),
            'biggest_win' => $openings->max('item_value'),
            'biggest_loss' => $openings->min('profit'),
            'average_case_price' => $openings->avg('case_price'),
            'average_win' => $openings->avg('item_value'),
            'jackpot_count' => $openings->where('is_jackpot', true)->count(),
            'today_openings' => $openings->whereDate('created_at', today())->count(),
            'this_week_openings' => $openings->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_openings' => $openings->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Get case statistics
     */
    public function getCaseStatistics(CaseModel $case): array
    {
        $openings = $case->openings();

        return [
            'total_openings' => $openings->count(),
            'total_revenue' => $openings->sum('case_price'),
            'total_paid_out' => $openings->sum('item_value'),
            'profit_margin' => $openings->count() > 0 ? 
                (($openings->sum('case_price') - $openings->sum('item_value')) / $openings->sum('case_price')) * 100 : 0,
            'unique_openers' => $openings->distinct('user_id')->count(),
            'today_openings' => $openings->whereDate('created_at', today())->count(),
            'this_week_openings' => $openings->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'average_opening_value' => $openings->avg('item_value'),
            'biggest_win' => $openings->max('item_value'),
            'jackpot_count' => $openings->where('is_jackpot', true)->count(),
            'profitable_openings_count' => $openings->where('profit', '>', 0)->count(),
            'loss_openings_count' => $openings->where('profit', '<', 0)->count(),
        ];
    }

    /**
     * Get global statistics
     */
    public function getGlobalStatistics(): array
    {
        $totalOpenings = CaseOpening::count();
        $totalUsers = User::count();
        $activeUsers = User::where('last_activity', '>=', now()->subDays(30))->count();

        return [
            'total_openings' => $totalOpenings,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_spent' => CaseOpening::sum('case_price'),
            'total_won' => CaseOpening::sum('item_value'),
            'total_profit' => CaseOpening::sum('profit'),
            'today_openings' => CaseOpening::whereDate('created_at', today())->count(),
            'today_revenue' => CaseOpening::whereDate('created_at', today())->sum('case_price'),
            'biggest_win_today' => CaseOpening::whereDate('created_at', today())->max('item_value'),
            'online_users' => User::where('last_activity', '>=', now()->subMinutes(5))->count(),
            'total_cases' => CaseModel::count(),
            'active_cases' => CaseModel::where('is_active', true)->count(),
            'total_items' => \App\Models\Item::count(),
            'active_items' => \App\Models\Item::where('is_active', true)->count(),
        ];
    }

    /**
     * Validate case opening constraints
     */
    public function validateCaseOpening(User $user, CaseModel $case): array
    {
        $errors = [];

        // Check if case is active
        if (!$case->is_active) {
            $errors[] = 'Case is not active';
        }

        // Check user level
        if ($user->level < $case->min_level) {
            $errors[] = "Minimum level required: {$case->min_level}";
        }

        // Check user balance
        if (!$user->canAfford($case->price)) {
            $errors[] = 'Insufficient balance';
        }

        // Check daily limits
        if ($case->max_daily_openings > 0) {
            $todayOpenings = $case->openings()
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayOpenings >= $case->max_daily_openings) {
                $errors[] = 'Daily opening limit reached';
            }
        }

        // Check if user is banned
        if ($user->is_banned) {
            $errors[] = 'User account is banned';
        }

        // Check anti-fraud limits
        $hourlyOpenings = $user->caseOpenings()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($hourlyOpenings >= config('app.max_hourly_cases', 100)) {
            $errors[] = 'Hourly opening limit reached';
        }

        return $errors;
    }

    /**
     * Calculate expected value for a case
     */
    public function calculateExpectedValue(CaseModel $case): float
    {
        $expectedValue = 0;
        $totalChance = 0;

        foreach ($case->items as $item) {
            $dropChance = $item->pivot->drop_chance;
            $expectedValue += ($item->price * $dropChance);
            $totalChance += $dropChance;
        }

        // Normalize to percentage
        if ($totalChance > 0) {
            $expectedValue = $expectedValue / $totalChance;
        }

        return $expectedValue;
    }

    /**
     * Get most recent openings for live feed
     */
    public function getRecentOpenings(int $limit = 50): \Illuminate\Support\Collection
    {
        return CaseOpening::with(['user', 'case', 'item'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($opening) {
                return [
                    'id' => $opening->id,
                    'user' => [
                        'id' => $opening->user->id,
                        'name' => $opening->user->name,
                        'avatar' => $opening->user->avatar,
                        'level' => $opening->user->level,
                    ],
                    'case' => [
                        'id' => $opening->case->id,
                        'name' => $opening->case->name,
                        'image' => $opening->case->image,
                        'price' => $opening->case->price,
                    ],
                    'item' => [
                        'id' => $opening->item->id,
                        'name' => $opening->item->name,
                        'image' => $opening->item->image,
                        'price' => $opening->item->price,
                        'rarity' => $opening->item->rarity,
                        'rarity_color' => $opening->item->getRarityColor(),
                    ],
                    'profit' => $opening->profit,
                    'is_jackpot' => $opening->is_jackpot,
                    'created_at' => $opening->created_at,
                    'time_ago' => $opening->time_ago,
                ];
            });
    }

    /**
     * Get big wins (jackpots) for display
     */
    public function getBigWins(int $limit = 20): \Illuminate\Support\Collection
    {
        return CaseOpening::with(['user', 'case', 'item'])
            ->where('is_jackpot', true)
            ->orderBy('item_value', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($opening) {
                return [
                    'id' => $opening->id,
                    'user' => [
                        'id' => $opening->user->id,
                        'name' => $opening->user->name,
                        'avatar' => $opening->user->avatar,
                        'level' => $opening->user->level,
                    ],
                    'case' => [
                        'id' => $opening->case->id,
                        'name' => $opening->case->name,
                        'image' => $opening->case->image,
                    ],
                    'item' => [
                        'id' => $opening->item->id,
                        'name' => $opening->item->name,
                        'image' => $opening->item->image,
                        'price' => $opening->item->price,
                        'rarity' => $opening->item->rarity,
                        'rarity_color' => $opening->item->getRarityColor(),
                    ],
                    'profit' => $opening->profit,
                    'multiplier' => round($opening->item_value / $opening->case_price, 2),
                    'created_at' => $opening->created_at,
                ];
            });
    }
}