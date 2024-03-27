<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtoExerciseComment extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'comment', 'sender', 'oto_program_id'];

    public function program()
    {
        return $this->belongsTo(User::class, 'oto_program_id');
    }
//
//    public function coach()
//    {
//        return $this->belongsTo(User::class, 'coach_id');
//    }
}
