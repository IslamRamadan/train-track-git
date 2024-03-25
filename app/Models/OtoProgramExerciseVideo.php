<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtoProgramExerciseVideo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'link', 'oto_program_exercise_id'];
}
