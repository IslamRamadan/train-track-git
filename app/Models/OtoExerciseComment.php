<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtoExerciseComment extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'comment', 'client_id', 'coach_id', 'sender'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
