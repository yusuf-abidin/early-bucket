<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceLog extends Model
{
    protected $fillable = [
        'performance_period_id',
        'regional_id',
        'area_id',
        'branch_id',
        'is_achieved',
    ];

    public function performancePeriod(): BelongsTo
    {
        return $this->belongsTo(PerformancePeriod::class);
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
