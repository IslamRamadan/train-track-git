<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseLogVideo extends Model
{
    use HasFactory;

    protected $fillable = ['exercise_log_id', 'path'];

}
