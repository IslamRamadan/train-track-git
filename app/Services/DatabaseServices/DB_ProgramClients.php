<?php

namespace App\Services\DatabaseServices;

use App\Models\ProgramClient;

class DB_ProgramClients
{

    public function create_program_client(mixed $program_id, mixed $client_id)
    {
        return ProgramClient::query()->create([
            'program_id' => $program_id,
            'client_id' => $client_id,
        ]);
    }

    public function delete_program_clients(mixed $program_id)
    {
        return ProgramClient::query()->where('program_id', $program_id)->delete();
    }
}
