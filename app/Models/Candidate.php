<?php
namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $guard = ['id'];
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
    ];

    public function article(){
    	return $this->belongsTo(Event::class);
    }
}