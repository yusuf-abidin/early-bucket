<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $fillable = [
        'name',
        'class'
    ];

    public function users(): HasMany
    {
        return $this->HasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->HasMany(Category::class);
    }
}
