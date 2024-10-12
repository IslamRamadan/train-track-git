<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymJoinRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'coach_id',
        'admin_id',
        'status',
    ];
}
