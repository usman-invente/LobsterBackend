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
        'sizeU',
        'sizeA',
        'sizeB',
        'sizeC',
        'sizeD',
        'sizeE',
        'user_id',
        'productId'
    ];

    protected $casts = [
        'offloadDate' => 'date',
        'totalKgOffloaded' => 'decimal:2',
        'totalKgReceived' => 'decimal:2',
        'totalKgDead' => 'decimal:2',
        'totalKgRotten' => 'decimal:2',
        'sizeU' => 'decimal:2',
        'sizeA' => 'decimal:2',
        'sizeB' => 'decimal:2',
        'sizeC' => 'decimal:2',
        'sizeD' => 'decimal:2',
        'sizeE' => 'decimal:2',
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