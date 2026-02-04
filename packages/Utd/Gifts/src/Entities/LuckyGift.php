<?php

namespace Utd\Gifts\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * LuckyGift Model
 * الهدايا المحظوظة (Lucky Gifts)
 */
class LuckyGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $table = 'lucky_gifts';

    protected $fillable = ['gift_id', 'win_probability', 'min_percentage'];

    protected $appends = ['min_percentag', 'mid_percentag', 'max_percentag'];

    /**
     * الحد الأدنى للنسبة
     */
    public function getMinPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[0] ?? 0);
    }

    /**
     * النسبة المتوسطة
     */
    public function getMidPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[1] ?? 0);
    }

    /**
     * الحد الأقصى للنسبة
     */
    public function getMaxPercentagAttribute(): int
    {
        return (int) (@explode(',', $this->min_percentage)[2] ?? 0);
    }

    /**
     * تعيين الحد الأدنى
     */
    public function setMinPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[0] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    /**
     * تعيين النسبة المتوسطة
     */
    public function setMidPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[1] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    /**
     * تعيين الحد الأقصى
     */
    public function setMaxPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[2] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    /**
     * الهدية المرتبطة
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    /**
     * Model events
     */
    protected static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            if ($model->win_probability === null) {
                return false;
            }

            $model->min_percentage = $model->min_percentag . ',' . $model->mid_percentag . ',' . $model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
        
        self::updating(function ($model) {
            if ($model->win_probability === null) {
                return false;
            }
            
            $model->min_percentage = $model->min_percentag . ',' . $model->mid_percentag . ',' . $model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
    }
}
