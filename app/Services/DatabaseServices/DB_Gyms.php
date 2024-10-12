<?php

namespace App\Services\DatabaseServices;

use App\Models\Gym;

class DB_Gyms
{


    public function create_gym(mixed $owner_id, mixed $name, mixed $description, mixed $logo)
    {
        return Gym::create([
            'owner_id' => $owner_id,
            'name' => $name,
            'description' => $description,
            'logo' => $logo
        ]);
    }
}
