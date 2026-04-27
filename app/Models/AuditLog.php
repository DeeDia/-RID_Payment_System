<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = true;
    const UPDATED_AT   = null;

    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'subject_id',
        'ip_address',
        'user_agent',
    ];
}
