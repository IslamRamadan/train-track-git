<?php

namespace App\Services\DatabaseServices;

use App\Models\Gym;

class DB_Gyms
{
    public function find_gym($gym_id)
    {
        return Gym::query()->with('package')->find($gym_id);
    }
    public function create_gym(mixed $owner_id, mixed $name, mixed $description, mixed $logo, $package_id = null)
    {
        return Gym::create([
            'owner_id' => $owner_id,
            'name' => $name,
            'description' => $description,
            'logo' => $logo,
            'package_id' => $package_id,
        ]);
    }

    public function update_gym_package($gym_id, $package_id)
    {
        return Gym::query()->where('id', $gym_id)->update(['package_id' => $package_id]);
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
