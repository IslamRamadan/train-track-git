<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GenderServices;

class GenderController extends Controller
{
    public function __construct(protected GenderServices $genderServices)
    {
    }

    public function list()
    {
        return $this->genderServices->list();
    }
}
