<?php

namespace App\Services\DatabaseServices;

use App\Models\Program;

class DB_Programs
{

    public function get_programs_with_coach(mixed $coach_id, $search)
    {
        return Program::with('program_types')->where('coach_id', $coach_id)
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })
            ->get();
    }

    public function add_program($coach_id, mixed $name, mixed $description)
    {
        return Program::create([
            'coach_id' => $coach_id,
            'name' => $name,
            'description' => $description,
            'program_type_id' => 1,
        ]);
    }

    public function find_program(mixed $program_id)
    {
        return Program::with('exercises.videos')->find($program_id);
    }

    public function update_program($program, mixed $name, mixed $description)
    {
        $program->update([
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function delete_program(mixed $program_id)
    {
        return Program::query()->where('id', $program_id)->delete();
    }
}
