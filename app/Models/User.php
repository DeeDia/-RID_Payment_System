<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'id_number',
        'account_number',
        'employee_id',
        'password',
        'role',          // 'customer' | 'employee'
    ];

    protected $hidden = [
        'password',      // Never expose bcrypt hash in JSON responses
        'remember_token',
        'id_number',     // Sensitive PII — hidden from serialisation
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Customers can have many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }
}
