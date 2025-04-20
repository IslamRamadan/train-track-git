<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachExerciseTemplateVideos extends Model
{
    use HasFactory;

    protected $fillable = ['template_id', 'link', 'title'];

    public function template()
    {
        return $this->belongsToMany(CoachExerciseTemplate::class, 'template_id');
    }

}
