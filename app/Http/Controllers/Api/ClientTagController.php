<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientTagServices;
use Illuminate\Http\Request;

class ClientTagController extends Controller
{
    public function __construct(protected ClientTagServices $clientTagServices)
    {

    }

    public function update(Request $request)
    {
        return $this->clientTagServices->update($request);
    }
}
