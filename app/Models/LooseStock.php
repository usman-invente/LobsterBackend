<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LooseStock extends Model
{
     protected $fillable = [
        'tankId',
        'size',
        'kg',
        'fromCrateId',
        'boatName',
        'offloadDate',
        'user_id',
    ];
    
    protected $casts = [
        'kg' => 'decimal:2',
        'offloadDate' => 'date',
    ];
    
    public function tank()
    {
        return $this->belongsTo(Tank::class, 'tankId');
    }
    
    public function crate()
    {
        return $this->belongsTo(Crate::class, 'fromCrateId');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
