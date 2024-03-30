<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneToOneProgram extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'coach_id', 'client_id'];
    protected $appends = ['exercises_number'];

    public function getExercisesNumberAttribute()
    {
        return OneToOneProgramExercise::where('one_to_one_program_id', $this->id)->count();
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

}
