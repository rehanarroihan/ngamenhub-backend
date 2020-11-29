<?php

namespace App\Models;

use App\Models\GroupMembers;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = "groups";

    protected $guard = ['id'];

    protected $fillable = [
        'picture', 'name', 'code', 'created_by'
    ];

    public function members() {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }
}