<?php

namespace App\Services\DatabaseServices;

use App\Models\ProgramClient;

class DB_ProgramClients
{

    public function create_program_client(mixed $program_id, mixed $client_id, $oto_program_id)
    {
        return ProgramClient::query()->create([
            'program_id' => $program_id,
            'client_id' => $client_id,
            'oto_program_id' => $oto_program_id,
        ]);
    }

    public function delete_program_clients(mixed $program_id)
    {
        return ProgramClient::query()->where('program_id', $program_id)->delete();
    }

    public function delete_program_clients_with_oto_id(mixed $oto_program_id)
    {
        return ProgramClient::query()->where('oto_program_id', $oto_program_id)->delete();
    }

    public function get_program_related_oto_programs(mixed $program_id)
    {
        return ProgramClient::query()->where(['program_id' => $program_id])->whereNotNull('oto_program_id')->get();
    }

}
