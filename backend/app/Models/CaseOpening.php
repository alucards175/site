<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseOpening extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'case_id',
        'item_id',
        'case_price',
        'item_value',
        'profit',
        'server_seed',
        'client_seed',
        'nonce',
        'hash',
        'float_value',
        'is_jackpot',
        'ip_address',
        'user_agent',
        'session_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'case_price' => 'decimal:2',
        'item_value' => 'decimal:2',
        'profit' => 'decimal:2',
        'float_value' => 'decimal:8',
        'is_jackpot' => 'boolean',
        'nonce' => 'integer',
    ];

    /**
     * User who opened the case
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Case that was opened
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    /**
     * Item that was won
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get openings from today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get openings from this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Get openings from this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Get profitable openings
     */
    public function scopeProfitable($query)
    {
        return $query->where('profit', '>', 0);
    }

    /**
     * Get jackpot wins
     */
    public function scopeJackpots($query)
    {
        return $query->where('is_jackpot', true);
    }

    /**
     * Get openings by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get recent openings for live feed
     */
    public function scopeForLiveFeed($query, $limit = 50)
    {
        return $query->with(['user', 'case', 'item'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Check if opening was profitable
     */
    public function isProfitable(): bool
    {
        return $this->profit > 0;
    }

    /**
     * Get profit percentage
     */
    public function getProfitPercentage(): float
    {
        if ($this->case_price == 0) {
            return 0;
        }

        return (($this->item_value - $this->case_price) / $this->case_price) * 100;
    }

    /**
     * Verify provably fair hash
     */
    public function verifyHash(): bool
    {
        $expectedHash = hash('sha256', $this->server_seed . ':' . $this->client_seed . ':' . $this->nonce);
        return $this->hash === $expectedHash;
    }

    /**
     * Get formatted profit
     */
    public function getFormattedProfitAttribute(): string
    {
        $sign = $this->profit >= 0 ? '+' : '';
        return $sign . number_format($this->profit, 2) . ' ₽';
    }

    /**
     * Get rarity class for styling
     */
    public function getRarityClass(): string
    {
        if (!$this->item) {
            return 'common';
        }

        return match($this->item->rarity) {
            Item::RARITY_CONSUMER => 'consumer',
            Item::RARITY_INDUSTRIAL => 'industrial',
            Item::RARITY_MIL_SPEC => 'mil-spec',
            Item::RARITY_RESTRICTED => 'restricted',
            Item::RARITY_CLASSIFIED => 'classified',
            Item::RARITY_COVERT => 'covert',
            Item::RARITY_CONTRABAND => 'contraband',
            Item::RARITY_SPECIAL => 'special',
            default => 'common',
        };
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($caseOpening) {
            // Calculate profit
            $caseOpening->profit = $caseOpening->item_value - $caseOpening->case_price;
            
            // Check if it's a jackpot (item value is 5x or more than case price)
            $caseOpening->is_jackpot = $caseOpening->item_value >= ($caseOpening->case_price * 5);
            
            // Generate session ID if not provided
            if (!$caseOpening->session_id) {
                $caseOpening->session_id = session()->getId();
            }
        });

        static::created(function ($caseOpening) {
            // Update user statistics
            $user = $caseOpening->user;
            $user->increment('total_spent', $caseOpening->case_price);
            $user->increment('total_won', $caseOpening->item_value);
            
            // Add experience points (1 point per ruble spent)
            $user->addExperience((int) $caseOpening->case_price);
            
            // Increment case total openings
            $caseOpening->case->incrementOpenings();
        });
    }

    /**
     * Get time ago formatted
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if this opening can be traded
     */
    public function canBeTradedBy(User $user): bool
    {
        return $this->user_id === $user->id && 
               $this->item->is_tradeable && 
               $this->created_at->addDays(7)->isPast(); // 7-day trade hold
    }

    /**
     * Get opening statistics for analytics
     */
    public static function getStatistics(array $filters = []): array
    {
        $query = self::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['case_id'])) {
            $query->where('case_id', $filters['case_id']);
        }

        return [
            'total_openings' => $query->count(),
            'total_spent' => $query->sum('case_price'),
            'total_won' => $query->sum('item_value'),
            'total_profit' => $query->sum('profit'),
            'profitable_openings' => $query->where('profit', '>', 0)->count(),
            'jackpot_openings' => $query->where('is_jackpot', true)->count(),
            'average_case_price' => $query->avg('case_price'),
            'average_item_value' => $query->avg('item_value'),
            'biggest_win' => $query->max('item_value'),
            'biggest_loss' => $query->min('profit'),
            'unique_users' => $query->distinct('user_id')->count(),
        ];
    }
}