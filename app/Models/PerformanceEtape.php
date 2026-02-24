<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEtape extends Model
{
    const TYPE_ETAPE_BC = 'komitmen_ETAPE_(CLQH/BC)';
    const TYPE_ETAPE_BM = 'komitmen_ETAPE_(RLQH/BM)';

    protected $fillable = [
        'branch_id',
        'etape_no',
        'user_id',
        'komitmen_etape_bc_id',
        'komitmen_etape_bm_id',
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

    public function komitmenEtapeBC(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'komitmen_etape_bc_id');
    }

    public function komitmenEtapeBM(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'komitmen_etape_bm_id');
    }
}
