<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'color_id',
        'order'
    ];

    protected $with = ['color'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public static function getNextOrder(string $type): int
    {
        return static::where('type', $type)->max('order') + 1;
    }

    public function tasks() : HasMany{
        return $this->hasMany(Task::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }
}
