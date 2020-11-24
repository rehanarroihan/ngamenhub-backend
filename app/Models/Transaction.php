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
}