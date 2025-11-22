<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crate extends Model
{
    protected $fillable = [
        'receiving_batch_id',
        'boatName',
        'offloadDate',
        'crateNumber',
        'size',
        'kg',
        'originalKg',
        'originalSize',
        'status',
        'tankId',
        'user_id',
    ];

     public function receivingBatch()
    {
        return $this->belongsTo(ReceivingBatch::class);
    }

    /**
     * Get the user that created this crate.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
