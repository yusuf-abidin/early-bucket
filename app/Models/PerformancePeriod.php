<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformancePeriod extends Model
{
    protected $fillable = [
        'month',
        'year',
        'performance_type',
        'start_date',
        'end_date',
    ];
}
