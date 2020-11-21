<?php

namespace App\Models;

use App\Models\Candidate;
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
        'photo_urls',
        'created_by'
    ];

    protected $casts = [
        'photo_urls' => 'array'
    ];

    public function candidates() {
        return $this->hasMany(Candidate::class, 'id_event', 'id');
    }
}