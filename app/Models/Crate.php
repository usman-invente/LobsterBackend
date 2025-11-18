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
        'user_id',
    ];
}
