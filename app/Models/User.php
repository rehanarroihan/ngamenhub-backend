<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Portfolio;
use App\Models\Group;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'role', 'full_name', 'email', 'phone',
        'password', 'bio', 'skills', 'picture'
    ];
    
    protected $hidden = ['password'];

    public function portfolios() {
        return $this->hasMany(Portfolio::class, 'user_id', 'id');
    }

    public function groups() {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }
}
