<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = "groups";

    protected $guard = ['id'];

    protected $fillable = [
        'picture', 'name', 'code', 'created_by'
    ];

    protected $appends = ['member_count'];

    public function members() {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }

    public function getMemberCountAttribute() {
        return count($this->members);
    }
}