<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'area_id',
        'name'
    ];

    public function area() : BelongsTo {
        return $this->belongsTo(Area::class);
    }

    public function performanceEtapes(): HasMany {
        return $this->hasMany(PerformanceEtape::class);
    }
}
