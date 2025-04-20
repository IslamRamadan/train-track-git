<?php

namespace App\Services\DatabaseServices;

use App\Models\Package;

class DB_Packages
{
    public function find_package($package_id)
    {
        return Package::query()->find($package_id);
    }

    public function get_appropriate_package($coach_active_clients, $operator = ">")
    {
        return Package::query()->where('clients_limit', $operator, $coach_active_clients)->orderBy('clients_limit')->first();
    }

    public function list_packages()
    {
        return Package::query()
            ->orderBy('amount')
            ->get();
    }
}
