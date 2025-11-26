<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchLineItem extends Model
{
    protected $fillable = [
        'dispatch_id',
        'tankId',
        'tankNumber',
        'crateId',
        'looseStockId',
        'size',
        'kg',
        'crateNumber',
        'isLoose',
    ];

     protected $casts = [
        'tankNumber' => 'integer',
        'kg' => 'decimal:2',
        'crateNumber' => 'integer',
        'isLoose' => 'boolean',
    ];

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function crate()
    {
        return $this->belongsTo(Crate::class, 'crateId');
    }
}
