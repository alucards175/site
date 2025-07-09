<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'steamid',
        'steam_profile_url',
        'balance',
        'level',
        'experience',
        'total_spent',
        'total_won',
        'referrer_id',
        'referral_code',
        'steam_trade_url',
        'is_banned',
        'ban_reason',
        'last_activity',
        'ip_address',
        'country',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'steamid',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity' => 'datetime',
        'balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'total_won' => 'decimal:2',
        'is_banned' => 'boolean',
        'level' => 'integer',
        'experience' => 'integer',
    ];

    /**
     * Case openings relationship
     */
    public function caseOpenings(): HasMany
    {
        return $this->hasMany(CaseOpening::class);
    }

    /**
     * User inventory relationship
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    /**
     * Deposits relationship
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Withdrawals relationship
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Referrer relationship
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Referrals relationship
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * Promo code uses relationship
     */
    public function promoCodeUses(): HasMany
    {
        return $this->hasMany(PromoCodeUse::class);
    }

    /**
     * User achievements relationship
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * User sessions relationship
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Generate unique referral code
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Get user level based on experience
     */
    public function getCurrentLevel(): int
    {
        return floor($this->experience / 1000) + 1;
    }

    /**
     * Get experience needed for next level
     */
    public function getExperienceForNextLevel(): int
    {
        $currentLevel = $this->getCurrentLevel();
        return ($currentLevel * 1000) - $this->experience;
    }

    /**
     * Add experience points
     */
    public function addExperience(int $points): void
    {
        $this->increment('experience', $points);
        $this->update(['level' => $this->getCurrentLevel()]);
    }

    /**
     * Check if user can afford amount
     */
    public function canAfford(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Deduct balance
     */
    public function deductBalance(float $amount): bool
    {
        if (!$this->canAfford($amount)) {
            return false;
        }

        $this->decrement('balance', $amount);
        return true;
    }

    /**
     * Add balance
     */
    public function addBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    /**
     * Get total case openings count
     */
    public function getTotalCaseOpenings(): int
    {
        return $this->caseOpenings()->count();
    }

    /**
     * Get recent wins
     */
    public function getRecentWins($limit = 10)
    {
        return $this->caseOpenings()
            ->with(['item', 'case'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user is online
     */
    public function isOnline(): bool
    {
        return $this->last_activity && $this->last_activity->diffInMinutes(now()) < 5;
    }

    /**
     * Update last activity
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Get avatar URL
     */
    public function getAvatarAttribute($value): string
    {
        return $value ?: 'https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/fe/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_full.jpg';
    }
}