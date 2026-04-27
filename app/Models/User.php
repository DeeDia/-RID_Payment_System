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
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'id_number',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }
}

