<?php

namespace App\Services\DatabaseServices;

use App\Models\Gender;

class DB_Genders
{

    public function getAllGenders()
    {
        return Gender::query()
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
    }
}
