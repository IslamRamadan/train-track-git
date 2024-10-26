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

    public function list_gyms(mixed $search)
    {
        $query = Gym::query();
        if (!empty($search)) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        return $query->get();
    }

    public function update_gym(mixed $gym, mixed $name, mixed $description)
    {
        $gym->update([
            'name' => $name,
            'description' => $description
        ]);
    }
}
