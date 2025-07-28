<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneToOneProgram extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'coach_id', 'client_id'];
    protected $appends = ['exercises_number','first_exercise_date'];

    public function getExercisesNumberAttribute()
    {
        return OneToOneProgramExercise::where('one_to_one_program_id', $this->id)->count();
    }

    public function getFirstExerciseDateAttribute()
    {
        return OneToOneProgramExercise::query()
            ->where('one_to_one_program_id', $this->id)
            ->orderBy('date')->first()->date ?? "";
    }

    public function exercises()
    {
        return $this->hasMany(OneToOneProgramExercise::class, 'one_to_one_program_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }


    public function comments()
    {
        return $this->hasMany(OtoExerciseComment::class, 'oto_program_id');
    }

    public function program_client()
    {
        return $this->hasOne(ProgramClient::class, 'oto_program_id');
    }

    public function starting_date()
    {
        return $this->hasOne(OneToOneProgramStartingDate::class, 'one_to_one_program_id');
    }
}
