<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Area extends Model
{
    protected $fillable = [
        'regional_id',
        'name'
    ];

    public function regional(): BelongsTo {
        return $this->belongsTo(Regional::class);
    }

    public function branches(): HasMany {
        return $this->hasMany(Branch::class);
    }

    protected static function booted(): void
    {
        static::updated(function ($area) {

            if (! $area->wasChanged('regional_id')) {
                return;
            }

            Branch::withoutEvents(function () use ($area) {
                $area->branches()->update([
                    'regional_id' => $area->regional_id,
                    'updated_at'  => now(),
                ]);
            });

        });
    }

    public function contactCluster(): HasOne
    {
        return $this->hasOne(ContactCluster::class);
    }

    public function performanceLogs(): HasMany {
        return $this->hasMany(PerformanceLog::class);
    }
}
