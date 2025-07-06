<?php

namespace App\Services;

use App\Http\Resources\CountryResource;
use App\Services\DatabaseServices\DB_Countries;
use Illuminate\Http\Request;

class CountryServices
{

    public function __construct(protected DB_Countries $DB_Countries, protected ValidationServices $validationServices)
    {
    }

    public function list(Request $request)
    {
        $this->validationServices->searchValidation($request);
        $search = $request->input('search', '');
        $countries = $this->DB_Countries->getAllCountries($search);
        $countriesResource = CountryResource::collection($countries);
        return response()->json($countriesResource);
    }
}
