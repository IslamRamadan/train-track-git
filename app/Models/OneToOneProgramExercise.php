<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneToOneProgramExercise extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'extra_description', 'one_to_one_program_id', 'arrangement', 'date', 'is_done','exercise_id'];

    public function one_to_one_program()
    {
        return $this->belongsTo(OneToOneProgram::class, 'one_to_one_program_id');
    }

    public function log()
    {
        return $this->hasOne(ExerciseLog::class, 'oto_exercise_id');
    }

    public function videos()
    {
        return $this->hasMany(OtoProgramExerciseVideo::class, 'oto_program_exercise_id');
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
