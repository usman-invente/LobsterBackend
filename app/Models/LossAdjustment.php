<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LossAdjustment extends Model
{
    protected $fillable = [
        'date',
        'tankId',
        'type',
        'size',
        'kg',
        'reason',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'tankNumber' => 'integer',
        'kg' => 'decimal:2',
    ];

    /**
     * Get the user that recorded this loss adjustment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include losses within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include specific loss type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include specific tank.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $tankNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTank($query, $tankNumber)
    {
        return $query->where('tankNumber', $tankNumber);
    }

    /**
     * Get the formatted loss type.
     *
     * @return string
     */
    public function getFormattedTypeAttribute()
    {
        return ucfirst($this->type);
    }

    /**
     * Get loss percentage of total stock (requires tank context).
     *
     * @param  float  $totalStock
     * @return float
     */
    public function getLossPercentage($totalStock)
    {
        if ($totalStock <= 0) {
            return 0;
        }
        return round(($this->kg / $totalStock) * 100, 2);
    }
}
