<?php

namespace App\Services;

use App\Http\Resources\GenderResource;
use App\Services\DatabaseServices\DB_Genders;
use Illuminate\Http\Request;

class GenderServices
{
    public function __construct(protected DB_Genders $DB_Genders)
    {

    }

    public function list()
    {
        $genders = $this->DB_Genders->getAllGenders();
        $gendersResource = GenderResource::collection($genders);
        return response()->json($gendersResource);
    }
}
