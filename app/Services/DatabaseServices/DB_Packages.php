<?php

namespace App\Services\DatabaseServices;

use App\Models\Package;
use App\Models\User;

class DB_Packages
{
    public function find_package($package_id)
    {
        return Package::query()->find($package_id);
    }
}
