<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymLeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id', 'coach_id','status'
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function scopeSearch(Builder $query, $search = null)
    {
        return $query->when($search !== null, function ($query) use ($search) {
            $query->whereHas('coach', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            })->orWhereHas('gym', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            })->orWhere('created_at', 'LIKE', '%' . $search . '%');
        });
    }
}
