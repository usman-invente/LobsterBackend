<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $fillable = [
        'type',
        'clientAwb',
        'dispatchDate',
        'totalKg',
        'sizes',
        'sizeU',
        'sizeA',
        'sizeB',
        'sizeC',
        'sizeD',
        'sizeE',
        'sizeM',
        'user_id',
    ];

     protected $casts = [
        'dispatchDate' => 'date',
        'totalKg' => 'decimal:2',
        'sizes' => 'array',
        'sizeU' => 'decimal:2',
        'sizeA' => 'decimal:2',
        'sizeB' => 'decimal:2',
        'sizeC' => 'decimal:2',
        'sizeD' => 'decimal:2',
        'sizeE' => 'decimal:2',
        'sizeM' => 'decimal:2',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lineItems()
    {
        return $this->hasMany(DispatchLineItem::class);
    }
}
