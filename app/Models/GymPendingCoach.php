<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymPendingCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'admin_id',
        'email'
    ];
}
