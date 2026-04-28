<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Regional extends Model
{
    protected $fillable = [
        'name',
    ];

    public function areas(): HasMany {
        return $this->hasMany(Area::class);
    }

    public function branches(): HasMany {
        return $this->hasMany(Branch::class);
    }

    public function contactCluster(): HasOne
    {
        return $this->hasOne(ContactCluster::class);
    }

    public function performanceLogs(): HasMany {
        return $this->hasMany(PerformanceLog::class);
    }
}
