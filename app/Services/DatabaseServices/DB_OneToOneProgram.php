<?php

namespace App\Services\DatabaseServices;

use App\Models\OneToOneProgram;

class DB_OneToOneProgram
{

    public function find_oto_program(mixed $program_id)
    {
        return OneToOneProgram::with('exercises.videos', 'comments')->find($program_id);
    }

    public function create_one_to_program($program_name, $program_description, mixed $client_id, mixed $coach_id)
    {
        return OneToOneProgram::query()->create([
            'name' => $program_name,
            'description' => $program_description,
            'coach_id' => $coach_id,
            'client_id' => $client_id,
        ]);
    }

    public function get_client_oto_programs(mixed $coach_id, mixed $client_id, mixed $search)
    {
        return OneToOneProgram::query()->where(['coach_id' => $coach_id, 'client_id' => $client_id])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })
            ->get();
    }

    public function get_all_client_oto_programs(mixed $client_id)
    {
        return OneToOneProgram::query()->where(['client_id' => $client_id])
            ->get();
    }

    public function delete_program(mixed $program_id)
    {
        return OneToOneProgram::query()->where('id', $program_id)->delete();
    }


    public function verify_coach_id($coach_id, $client_program_id)
    {
        return OneToOneProgram::query()
            ->where([
                'id' => $client_program_id,
                'coach_id' => $coach_id,
            ])
            ->exists();
    }

    public function update_oto_program($program, mixed $name, mixed $description)
    {
        return $program->update([
            'name' => $name,
            'description' => $description
        ]);
    }

}
