<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencySalary extends Model
{
    protected $table = 'agency_sallaries';

    protected $guarded = [];

    protected $casts = [
        'sallary' => 'float',
        'cut_amount' => 'float',
        'is_paid' => 'boolean',
        'month' => 'integer',
        'year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Agency
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * Get net salary
     */
    public function getNetSalaryAttribute(): float
    {
        return $this->sallary - $this->cut_amount;
    }

    /**
     * Scope for unpaid salaries
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', 0);
    }

    /**
     * Scope for paid salaries
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', 1);
    }

    /**
     * Scope by month and year
     */
    public function scopeForPeriod($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): bool
    {
        $this->is_paid = true;

        return $this->save();
    }

    /**
     * Get total salary attribute (backward compatibility)
     */
    public function getTotalSalaryAttribute()
    {
        return floor($this->sallary - $this->cut_amount);
    }

    /**
     * Total target diamonds relationship
     */
    public function totalTargetDiamonds($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $userSalaryClass = config('agency-package.models.user_salary', \App\Models\UserSallary::class);

        return $this->hasMany($userSalaryClass, 'user_agency_id', 'agency_id')
            ->where('month', $month)
            ->where('year', $year)
            ->selectRaw('user_agency_id, SUM(target_diamonds) as total_target_diamonds')
            ->groupBy('user_agency_id');
    }

    /**
     * Boot model events
     */
    protected static function booted()
    {
        self::saved(function ($model) {
            if ($model->agency_id && function_exists('clearAgencyCache')) {
                clearAgencyCache($model->agency_id);
            }
        });

        self::deleted(function ($model) {
            if ($model->agency_id && function_exists('clearAgencyCache')) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
