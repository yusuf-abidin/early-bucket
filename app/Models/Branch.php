<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    protected $fillable = [
        'regional_id',
        'area_id',
        'name'
    ];

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }

    public function area(): BelongsTo {
        return $this->belongsTo(Area::class);
    }

    public function isDirectToRegional(): bool
    {
        return is_null($this->area_id);
    }

    public function performanceEtapes(): HasMany {
        return $this->hasMany(PerformanceEtape::class);
    }

    protected static function booted(): void
    {
        static::saving(function ($branch) {
            if ($branch->area_id) {
                $area = Area::find($branch->area_id);
                $branch->regional_id = $area->regional_id;
            }
        });
    }

    public function contactCluster(): HasOne
    {
        return $this->hasOne(ContactCluster::class);
    }

    public function stcTlContacts(): HasMany
    {
        return $this->hasMany(StcTlContact::class);
    }

    public function performanceLogs(): HasMany {
        return $this->hasMany(PerformanceLog::class);
    }
}
