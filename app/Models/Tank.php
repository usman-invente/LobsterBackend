<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
     protected $fillable = [
        'tankNumber',
        'tankName',
        'status',
        // add any other columns you want to allow for mass assignment
    ];


    public function crates()
    {
        return $this->hasMany(\App\Models\Crate::class, 'tankId');
    }

    public function looseStock()
    {
        return $this->hasMany(\App\Models\LooseStock::class, 'tankId');
    }
}
