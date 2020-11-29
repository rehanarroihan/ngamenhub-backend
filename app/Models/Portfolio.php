<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table = "portfolios";

    protected $guard = ['id'];

    protected $fillable = [
        'user_id', 'video_file_name'
    ];

    public function users() {
        return $this->belongsTo(User::class);
    }
}
