<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guard = ['id'];

    protected $fillable = [
        'user_id',
        'candidate_id',
        'event_id',
        'status'
    ];

    public function customer() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function candidate() {
        return $this->hasOne(User::class, 'id', 'candidate_id');
    }

    public function event() {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}