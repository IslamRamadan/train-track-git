<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramClient extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'client_id', 'oto_program_id'];
}
