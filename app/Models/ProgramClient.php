<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramClient extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'client_id', 'oto_program_id'];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function oto_program()
    {
        return $this->belongsTo(OneToOneProgram::class, 'oto_program_id');
    }
}
