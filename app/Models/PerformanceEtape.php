<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEtape extends Model
{
    protected $fillable = [
        'branch_id',
        'etape_no',
        'user_id',
        'komitmen_etape_id',
        'komitmen_eom_bc_id',
        'komitmen_eom_bm_id',
        'prognosa_akhir_bulan',
        'kendala',
        'year',
        'month'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'etape_no' => 'integer',
    ];

    public function branch() : BelongsTo {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function komitmenEtape(): BelongsTo {
        return $this->belongsTo(Category::class, 'komitmen_etape_id');
    }

    public function komitmenEomBc(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'komitmen_eom_bc_id');
    }

    public function komitmenEomBm(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'komitmen_eom_bm_id');
    }
}
