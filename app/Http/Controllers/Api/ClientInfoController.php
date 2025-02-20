<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientInfoServices;
use Illuminate\Http\Request;

class ClientInfoController extends Controller
{
    public function __construct(protected ClientInfoServices $clientInfoServices)
    {

    }

    public function update(Request $request)
    {
        return $this->clientInfoServices->update($request);
    }
}
