<?php

namespace App\Services\DatabaseServices;

use App\Models\Country;

class DB_Countries
{

    public function getAllCountries($search)
    {
        return Country::query()
            ->select('id', 'name', 'code')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('code', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('name')
            ->get();
    }
}
