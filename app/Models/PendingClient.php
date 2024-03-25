<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingClient extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'email'];
}
