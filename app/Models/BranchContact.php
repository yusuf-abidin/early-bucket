<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchContact extends Model
{
    protected $fillable = [
        'regional_id',
        'branch_name',
        'name',
        'nip',
        'phone',
        'avatar'
    ];

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }
}
