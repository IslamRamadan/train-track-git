<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'coach_id',
        'privilege'
    ];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
