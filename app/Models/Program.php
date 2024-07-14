<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'coach_id', 'program_type_id', 'type', 'starting_date', 'sync', 'image'];
    protected $appends = ['exercise_days', 'clients_number', 'type_text','image_path'];

    public function program_types()
    {
        return $this->belongsTo(ProgramType::class, 'program_type_id');
    }

    public function exercises()
    {
        return $this->hasMany(ProgramExercise::class, 'program_id');
    }

    public function one_to_one_program()
    {
        return $this->hasMany(ProgramClient::class, 'program_id');
    }

    public function getExerciseDaysAttribute()
    {
        return ProgramExercise::where('program_id', $this->id)->distinct('day')->count();
    }

    public function getClientsNumberAttribute()
    {
        return ProgramClient::where('program_id', $this->id)->distinct('client_id')->count();
    }

    protected function typeText(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->type == "0" ? "Normal" : "Ongoing",
        );
    }
    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->image ? asset('storage/app/public/programs/' . $this->image) : "",
        );
    }

}
