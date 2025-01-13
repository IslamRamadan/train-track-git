<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachVideo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'link', 'coach_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function videos()
    {
        return $this->hasMany(CoachExerciseTemplateVideos::class, 'video_id');
    }

}
