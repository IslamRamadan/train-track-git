<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramExercise extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'extra_description', 'day', 'arrangement', 'program_id'];

    public function videos()
    {
        return $this->hasMany(ProgramExerciseVideo::class, 'program_exercise_id');
    }

    protected function extraDescription(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value != null ? $value : "",
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value != null ? $value : "",
        );
    }
}
