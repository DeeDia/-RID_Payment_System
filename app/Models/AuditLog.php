<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog — immutable audit trail for all sensitive actions.
 *
 * Records: who performed an action, what action, on what subject,
 * from which IP and User-Agent, and when.
 */
class AuditLog extends Model
{
    // Audit logs are NEVER updated or deleted
    public $timestamps = true;
    const UPDATED_AT   = null; // Only created_at — no updates

    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'subject_id',
        'ip_address',
        'user_agent',
    ];
}
