<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymPendingCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'admin_id',
        'email'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function scopeSearch(Builder $query, $search = null)
    {
        return $query->when($search !== null, function ($query) use ($search) {
            $query->whereHas('gym', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            })->orWhereHas('admin', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            })->orWhere('created_at', 'LIKE', '%' . $search . '%');
        });
    }
}
