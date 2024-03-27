<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramExerciseVideo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'link', 'program_exercise_id'];

}
