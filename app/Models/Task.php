<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    const TYPE_PENDING = 'pending_matter';
    const TYPE_DEBITUR = 'debitur_menabung';

    const SCOPE_RLQH = 'rlqh';
    const SCOPE_CENTRAL = 'central';
    protected $fillable = [
        'scope',
        'type',
        'task_description',
        'category_id',
        'due_date',
        'due_date_updated_at',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
        'due_date_updated_at' => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d',
        'completed_at' => 'datetime:Y-m-d',
    ];

    public function category() : BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function users() : BelongsToMany {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function isCompleted() : bool{
        return $this->completed_at !== null;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            if($model->isDirty('due_date')) {
                $model->due_date_updated_at = now();
            }
        });
    }
}
