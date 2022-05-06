<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements Authenticatable
{
    use HasApiTokens, HasFactory, \Illuminate\Auth\Authenticatable, ReturnRegionDateTime;

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
