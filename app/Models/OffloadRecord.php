<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffloadRecord extends Model
{
    protected $fillable = [
        'boatName',
        'offloadDate',
        'tripNumber',
        'externalFactory',
        'totalKgOffloaded',
        'totalKgReceived',
        'totalKgDead',
        'totalKgRotten',
        'totalLive',
        'user_id',
        'productId',
        'sizes',
    ];

    protected $casts = [
        'offloadDate' => 'date',
        'totalKgOffloaded' => 'decimal:2',
        'totalKgReceived' => 'decimal:2',
        'totalKgDead' => 'decimal:2',
        'totalKgRotten' => 'decimal:2',
         'sizes' => 'array',
      
    ];

    /**
     * Get the user that created the offload record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
{
    return $this->belongsTo(Product::class);
}
}