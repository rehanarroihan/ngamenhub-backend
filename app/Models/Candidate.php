<?php
namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $table = "candidates";

    protected $guard = ['id'];

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
    ];

    public function event(){
    	return $this->belongsTo(Event::class);
    }

    protected $appends = ['full_name', 'email'];

    protected $hidden = ['userDetail'];

    public function getFullNameAttribute() {
        return $this->userDetail->full_name;
    }

    public function getEmailAttribute() {
        return $this->userDetail->email;
    }

    public function userDetail() {
        return $this->belongsTo(User::class, 'user_id');
    }
}