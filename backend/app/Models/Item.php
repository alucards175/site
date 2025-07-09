<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

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
        'market_price',
        'rarity',
        'category',
        'exterior',
        'float_min',
        'float_max',
        'steam_market_name',
        'steam_asset_id',
        'is_active',
        'is_tradeable',
        'is_marketable',
        'inspect_link',
        'collection',
        'wear_rating',
        'pattern_index',
        'rarity_color',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'float_min' => 'decimal:8',
        'float_max' => 'decimal:8',
        'is_active' => 'boolean',
        'is_tradeable' => 'boolean',
        'is_marketable' => 'boolean',
        'pattern_index' => 'integer',
    ];

    /**
     * Item rarity constants
     */
    const RARITY_CONSUMER = 'consumer';
    const RARITY_INDUSTRIAL = 'industrial';
    const RARITY_MIL_SPEC = 'mil_spec';
    const RARITY_RESTRICTED = 'restricted';
    const RARITY_CLASSIFIED = 'classified';
    const RARITY_COVERT = 'covert';
    const RARITY_CONTRABAND = 'contraband';
    const RARITY_SPECIAL = 'special';

    /**
     * Exterior constants
     */
    const EXTERIOR_FACTORY_NEW = 'factory_new';
    const EXTERIOR_MINIMAL_WEAR = 'minimal_wear';
    const EXTERIOR_FIELD_TESTED = 'field_tested';
    const EXTERIOR_WELL_WORN = 'well_worn';
    const EXTERIOR_BATTLE_SCARRED = 'battle_scarred';

    /**
     * Cases that contain this item
     */
    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(CaseModel::class, 'case_items')
            ->withPivot('drop_chance', 'min_float', 'max_float')
            ->withTimestamps();
    }

    /**
     * Case openings where this item was won
     */
    public function caseOpenings(): HasMany
    {
        return $this->hasMany(CaseOpening::class);
    }

    /**
     * User inventories containing this item
     */
    public function userInventories(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    /**
     * Get active items only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get items by rarity
     */
    public function scopeByRarity($query, $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Get items by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get items by price range
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Get tradeable items only
     */
    public function scopeTradeable($query)
    {
        return $query->where('is_tradeable', true);
    }

    /**
     * Get marketable items only
     */
    public function scopeMarketable($query)
    {
        return $query->where('is_marketable', true);
    }

    /**
     * Get rarity color
     */
    public function getRarityColor(): string
    {
        return match($this->rarity) {
            self::RARITY_CONSUMER => '#b0c3d9',
            self::RARITY_INDUSTRIAL => '#5e98d9',
            self::RARITY_MIL_SPEC => '#4b69ff',
            self::RARITY_RESTRICTED => '#8847ff',
            self::RARITY_CLASSIFIED => '#d32ce6',
            self::RARITY_COVERT => '#eb4b4b',
            self::RARITY_CONTRABAND => '#e4ae39',
            self::RARITY_SPECIAL => '#ffd700',
            default => '#b0c3d9',
        };
    }

    /**
     * Get rarity display name
     */
    public function getRarityDisplayName(): string
    {
        return match($this->rarity) {
            self::RARITY_CONSUMER => 'Consumer Grade',
            self::RARITY_INDUSTRIAL => 'Industrial Grade',
            self::RARITY_MIL_SPEC => 'Mil-Spec Grade',
            self::RARITY_RESTRICTED => 'Restricted',
            self::RARITY_CLASSIFIED => 'Classified',
            self::RARITY_COVERT => 'Covert',
            self::RARITY_CONTRABAND => 'Contraband',
            self::RARITY_SPECIAL => 'Special',
            default => 'Unknown',
        };
    }

    /**
     * Get exterior display name
     */
    public function getExteriorDisplayName(): string
    {
        return match($this->exterior) {
            self::EXTERIOR_FACTORY_NEW => 'Factory New',
            self::EXTERIOR_MINIMAL_WEAR => 'Minimal Wear',
            self::EXTERIOR_FIELD_TESTED => 'Field-Tested',
            self::EXTERIOR_WELL_WORN => 'Well-Worn',
            self::EXTERIOR_BATTLE_SCARRED => 'Battle-Scarred',
            default => 'Unknown',
        };
    }

    /**
     * Generate random float value for this item
     */
    public function generateFloat(): float
    {
        $min = $this->float_min ?: 0.0;
        $max = $this->float_max ?: 1.0;
        
        return round(mt_rand($min * 100000000, $max * 100000000) / 100000000, 8);
    }

    /**
     * Get image URL
     */
    public function getImageAttribute($value): string
    {
        if (!$value) {
            return '/images/items/default.png';
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return '/storage/items/' . $value;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ₽';
    }

    /**
     * Get formatted market price
     */
    public function getFormattedMarketPriceAttribute(): string
    {
        return number_format($this->market_price ?? $this->price, 2) . ' ₽';
    }

    /**
     * Check if item price is above market average
     */
    public function isProfitable(): bool
    {
        return $this->market_price && $this->price < $this->market_price;
    }

    /**
     * Get total times this item was won
     */
    public function getTotalWins(): int
    {
        return $this->caseOpenings()->count();
    }

    /**
     * Get times this item was won today
     */
    public function getTodayWins(): int
    {
        return $this->caseOpenings()
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get item statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_wins' => $this->getTotalWins(),
            'today_wins' => $this->getTodayWins(),
            'unique_winners' => $this->caseOpenings()->distinct('user_id')->count(),
            'last_won_at' => $this->caseOpenings()->latest()->first()?->created_at,
            'average_case_price' => $this->cases()->avg('price'),
            'profit_margin' => $this->market_price ? 
                (($this->market_price - $this->price) / $this->price) * 100 : 0,
        ];
    }

    /**
     * Get rarity ordering for sorting
     */
    public static function getRarityOrder(): array
    {
        return [
            self::RARITY_CONSUMER => 1,
            self::RARITY_INDUSTRIAL => 2,
            self::RARITY_MIL_SPEC => 3,
            self::RARITY_RESTRICTED => 4,
            self::RARITY_CLASSIFIED => 5,
            self::RARITY_COVERT => 6,
            self::RARITY_CONTRABAND => 7,
            self::RARITY_SPECIAL => 8,
        ];
    }

    /**
     * Sort by rarity (most rare first)
     */
    public function scopeOrderByRarity($query, $direction = 'desc')
    {
        $rarityOrder = self::getRarityOrder();
        
        return $query->orderByRaw(
            "CASE 
                WHEN rarity = '" . self::RARITY_CONSUMER . "' THEN " . $rarityOrder[self::RARITY_CONSUMER] . "
                WHEN rarity = '" . self::RARITY_INDUSTRIAL . "' THEN " . $rarityOrder[self::RARITY_INDUSTRIAL] . "
                WHEN rarity = '" . self::RARITY_MIL_SPEC . "' THEN " . $rarityOrder[self::RARITY_MIL_SPEC] . "
                WHEN rarity = '" . self::RARITY_RESTRICTED . "' THEN " . $rarityOrder[self::RARITY_RESTRICTED] . "
                WHEN rarity = '" . self::RARITY_CLASSIFIED . "' THEN " . $rarityOrder[self::RARITY_CLASSIFIED] . "
                WHEN rarity = '" . self::RARITY_COVERT . "' THEN " . $rarityOrder[self::RARITY_COVERT] . "
                WHEN rarity = '" . self::RARITY_CONTRABAND . "' THEN " . $rarityOrder[self::RARITY_CONTRABAND] . "
                WHEN rarity = '" . self::RARITY_SPECIAL . "' THEN " . $rarityOrder[self::RARITY_SPECIAL] . "
                ELSE 0 
            END " . $direction
        );
    }
}