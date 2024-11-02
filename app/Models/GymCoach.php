<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'coach_id',
        'privilege'
    ];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    protected function privilegeText(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match ($this->privilege) {
                "1" => "Owner",
                "2" => "Admin",
                default => "Coach",
            },
        );
    }
}
