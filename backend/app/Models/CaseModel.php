<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'category',
        'is_active',
        'is_featured',
        'min_level',
        'max_daily_openings',
        'sort_order',
        'total_openings',
        'house_edge',
        'rtp_percentage',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'min_level' => 'integer',
        'max_daily_openings' => 'integer',
        'sort_order' => 'integer',
        'total_openings' => 'integer',
        'house_edge' => 'decimal:4',
        'rtp_percentage' => 'decimal:2',
    ];

    /**
     * Case openings relationship
     */
    public function openings(): HasMany
    {
        return $this->hasMany(CaseOpening::class, 'case_id');
    }

    /**
     * Case items relationship (pivot table)
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'case_items')
            ->withPivot('drop_chance', 'min_float', 'max_float')
            ->withTimestamps();
    }

    /**
     * Get active cases only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get featured cases only
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get cases by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get cases accessible by user level
     */
    public function scopeAccessibleByLevel($query, $userLevel)
    {
        return $query->where('min_level', '<=', $userLevel);
    }

    /**
     * Get total value of all items in case
     */
    public function getTotalValue(): float
    {
        return $this->items->sum('price');
    }

    /**
     * Get average item value
     */
    public function getAverageItemValue(): float
    {
        $items = $this->items;
        return $items->count() > 0 ? $items->avg('price') : 0;
    }

    /**
     * Get most valuable item in case
     */
    public function getMostValuableItem()
    {
        return $this->items->sortByDesc('price')->first();
    }

    /**
     * Get random item based on drop chances (Provably Fair)
     */
    public function getRandomItem($serverSeed, $clientSeed, $nonce): ?Item
    {
        $items = $this->items()->get();
        
        if ($items->isEmpty()) {
            return null;
        }

        // Create hash for provably fair
        $hash = hash('sha256', $serverSeed . ':' . $clientSeed . ':' . $nonce);
        $decimal = hexdec(substr($hash, 0, 8)) / (pow(16, 8) - 1);

        // Calculate cumulative probabilities
        $totalChance = $items->sum('pivot.drop_chance');
        $random = $decimal * $totalChance;
        
        $cumulative = 0;
        foreach ($items as $item) {
            $cumulative += $item->pivot->drop_chance;
            if ($random <= $cumulative) {
                return $item;
            }
        }

        // Fallback to first item
        return $items->first();
    }

    /**
     * Check if user can open this case
     */
    public function canBeOpenedBy(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($user->level < $this->min_level) {
            return false;
        }

        if ($this->max_daily_openings > 0) {
            $todayOpenings = $this->openings()
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayOpenings >= $this->max_daily_openings) {
                return false;
            }
        }

        return $user->canAfford($this->price);
    }

    /**
     * Get case statistics
     */
    public function getStatistics(): array
    {
        $openings = $this->openings();
        
        return [
            'total_openings' => $openings->count(),
            'today_openings' => $openings->whereDate('created_at', today())->count(),
            'total_value_won' => $openings->sum('item_value'),
            'average_value_won' => $openings->avg('item_value'),
            'most_valuable_win' => $openings->max('item_value'),
            'unique_users' => $openings->distinct('user_id')->count(),
        ];
    }

    /**
     * Get recent openings
     */
    public function getRecentOpenings($limit = 10)
    {
        return $this->openings()
            ->with(['user', 'item'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Update total openings counter
     */
    public function incrementOpenings(): void
    {
        $this->increment('total_openings');
    }

    /**
     * Get image URL
     */
    public function getImageAttribute($value): string
    {
        if (!$value) {
            return '/images/cases/default.png';
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return '/storage/cases/' . $value;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ₽';
    }

    /**
     * Check if case is profitable (RTP check)
     */
    public function isProfitable(): bool
    {
        $expectedValue = 0;
        foreach ($this->items as $item) {
            $expectedValue += ($item->price * ($item->pivot->drop_chance / 100));
        }
        
        return $expectedValue > $this->price;
    }
}