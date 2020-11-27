<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Portfolio;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'role', 'full_name', 'email', 'phone',
        'password', 'bio', 'skills', 'picture'
    ];
    
    protected $hidden = ['password'];

    protected $with = ['portofolios'];

    public function portofolios() {
        return $this->hasMany(Portfolio::class, 'user_id', 'id');
    }
}
