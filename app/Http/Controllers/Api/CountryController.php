<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CountryServices;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(protected CountryServices $countryServices)
    {
    }
    public function list(Request $request)
    {
        return $this->countryServices->list($request);
    }
}
