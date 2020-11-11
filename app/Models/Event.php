<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guard = ['id'];
    protected $fillable = [
        'name',
        'description',
        'type',
        'date',
        'fee',
        'address',
        'photo_url',
        'created_by'
    ];
}