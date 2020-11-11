<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $guard = ['id'];
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
    ];
}