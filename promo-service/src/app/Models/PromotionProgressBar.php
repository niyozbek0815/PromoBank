<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionProgressBar extends Model
{
    use HasFactory;

    protected $table = 'promotion_progress_bars';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'promotion_id',
        'daily_points',
        'step_0_threshold',
        'step_1_threshold',
        'step_2_threshold',
        'day_start_at',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'promotion_id' => 'integer',
        'daily_points' => 'integer',
        'step_0_threshold' => 'integer',
        'step_1_threshold' => 'integer',
        'step_2_threshold' => 'integer',
    ];

    /**
     * Relation to Promotion
     */
    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    /**
     * Get the thresholds as an array for easy use in code
     */
    public function getThresholds(): array
    {
        return [
            0 => $this->step_0_threshold,
            1 => $this->step_1_threshold,
            2 => $this->step_2_threshold,
        ];
    }

    /**
     * Scope: Only progress bars starting today
     */
    // public function scopeToday($query)
    // {
    //     return $query->whereDate('created_at', now()->toDateString());
    // }

    /**
     * Optional helper: check if step is achieved based on points
     */
    public function stepAchieved(int $points): int
    {
        if ($points >= $this->step_2_threshold) {
            return 2;
        }
        if ($points >= $this->step_1_threshold) {
            return 1;
        }
        if ($points >= $this->step_0_threshold) {
            return 0;
        }

        return -1; // Hech qanday stepga yetmagan
    }
}
