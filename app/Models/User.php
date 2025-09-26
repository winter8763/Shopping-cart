<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, MustVerifyEmailTrait;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'address',
        'role',
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
