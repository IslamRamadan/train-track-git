<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = ['sets', 'details', 'oto_exercise_id', 'client_id'];

    public function exercise()
    {
        return $this->belongsTo(OneToOneProgramExercise::class, 'oto_exercise_id');
    }

    public function log_videos()
    {
        return $this->hasMany(ExerciseLogVideo::class, 'exercise_log_id');
    }
    protected function sets(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value != null ? $value : "",
        );
    }
}
