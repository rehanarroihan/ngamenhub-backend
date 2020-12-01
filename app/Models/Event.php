<?php

namespace App\Models;

use App\Models\Candidate;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = "events";

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

    protected $appends = ['candidate_count', 'transaction_id'];

    public function getCandidateCountAttribute() {
        return count($this->candidates);
    }

    public function getTransactionIdAttribute() {
        if ($this->transaction) {
            return $this->transaction->id;
        } else {
            return null;
        }
    }

    public function candidates() {
        return $this->hasMany(Candidate::class, 'event_id', 'id');
    }

    public function transaction() {
        return $this->hasOne(Transaction::class, 'event_id');
    }
}