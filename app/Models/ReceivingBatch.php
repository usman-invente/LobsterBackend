<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingBatch extends Model
{
    protected $fillable = [
        'date',
        'batchNumber',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crates()
    {
        return $this->hasMany(Crate::class);
    }
}
