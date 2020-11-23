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
        'photo_urls' => 'array',
        'address' => 'array'
    ];

    protected $with = ['candidates'];

    protected $appends = ['candidate_count'];

    public function getCandidateCountAttribute() {
        return count($this->candidates);
    }

    public function candidates() {
        return $this->hasMany(Candidate::class, 'event_id', 'id');
    }
}