<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachExerciseTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'coach_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function videos()
    {
        return $this->hasMany(CoachExerciseTemplateVideos::class, 'template_id');
    }
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value != null ? $value : "",
        );
    }
}
