<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Memo extends Model
{
    protected $fillable = [
        'received_at',
        'origin',
        'reference_number',
        'subject',
        'category_id',
        'document_link',
        'completed_at',
        'due_date',
        'follow_up_note'
    ];


    protected $casts = [
        'received_at' => 'datetime:Y-m-d',
        'completed_at' => 'datetime:Y-m-d',
        'due_date' => 'datetime:Y-m-d',
    ];

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'memo_user')->withTimestamps();
    }

    public function scopeFilterAndSort($query, $request)
    {
        return $query
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('origin', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('follow_up_note', 'like', "%{$search}%");
                });
            })
            ->when($request->date_from, function ($query, $date){
                $query->whereDate('received_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date){
                $query->whereDate('received_at', '<=', $date);
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('users.id', $request->user_id);
                });
            })
            ->when($request->filled('user_ids'), function ($query) use ($request) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->whereIn('users.id', $request->user_ids);
                });
            });
    }

    public function scopeApplySort($query, $sortBy = 'received_at', $sortDir = 'desc')
    {
        if($sortBy === 'category') {
            return $query
                ->join('categories', 'categories.id', '=', 'memos.category_id')
                ->where('categories.type', 'memo')
                ->orderBy('categories.order', $sortDir)
                ->select('memos.*');
        }

        return $query->orderBy("memos.{$sortBy}", $sortDir);
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }
}
