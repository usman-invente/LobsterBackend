<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffloadRecord extends Model
{
     protected $fillable = [
        'date',
        'boatName',
        'boatNumber',
        'captainName',
        'totalCrates',
        'totalKgAlive',
        'sizeU',
        'sizeA',
        'sizeB',
        'sizeC',
        'sizeD',
        'sizeE',
        'deadOnTanks',
        'rottenOnTanks',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'totalCrates' => 'integer',
        'totalKgAlive' => 'decimal:2',
        'sizeU' => 'decimal:2',
        'sizeA' => 'decimal:2',
        'sizeB' => 'decimal:2',
        'sizeC' => 'decimal:2',
        'sizeD' => 'decimal:2',
        'sizeE' => 'decimal:2',
        'deadOnTanks' => 'decimal:2',
        'rottenOnTanks' => 'decimal:2',
    ];

      /**
     * Get the user that created the offload record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
}
