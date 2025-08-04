<?php

namespace App\Services\DatabaseServices;

use App\Models\OneToOneProgramStartingDate;

class DB_OneToOneProgramStartingDate
{

    public function create_starting_date(mixed $program_id, mixed $starting_date)
    {
        return OneToOneProgramStartingDate::query()
            ->create([
                'one_to_one_program_id' => $program_id,
                'starting_date' => $starting_date,
            ]);
    }

}
