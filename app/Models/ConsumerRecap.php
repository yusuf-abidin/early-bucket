<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumerRecap extends Model
{
    protected $fillable = [
        'date',
        'month',
        'year',
        'consumer',
        'percent'
    ];
}
