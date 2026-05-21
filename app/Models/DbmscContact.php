<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DbmscContact extends Model
{
    protected $fillable = [
        'branch_contact_id',
        'name',
        'nip',
        'phone',
        'avatar'
    ];

    public function branchContact()
    {
        return $this->belongsTo(BranchContact::class);
    }
}
