<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersPayment extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'order_id', 'amount', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
