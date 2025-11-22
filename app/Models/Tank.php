<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    public function crates()
    {
        return $this->hasMany(\App\Models\Crate::class, 'tankId');
    }

    public function looseStock()
    {
        return $this->hasMany(\App\Models\LooseStock::class, 'tankId');
    }
}
