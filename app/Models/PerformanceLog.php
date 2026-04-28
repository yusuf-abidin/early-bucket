<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    protected $fillable = [
        'performance_period_id',
        'regional_id',
        'area_id',
        'branch_id',
        'is_achieved',
    ];
}
