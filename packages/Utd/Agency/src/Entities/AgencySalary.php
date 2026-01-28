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
}
