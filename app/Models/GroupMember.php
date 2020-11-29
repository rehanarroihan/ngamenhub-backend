<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $table = "group_members";

    protected $guard = ['id'];
    
    protected $fillable = [
        'group_id', 'user_id'
    ];
}
