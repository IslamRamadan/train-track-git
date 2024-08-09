<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthServices;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthServices $authServices)
    {
    }

    public function login(Request $request)
    {
        return $this->authServices->login($request);
    }

    public function client_register(Request $request)
    {
        return $this->authServices->client_register($request);
    }

    public function coach_register(Request $request)
    {
        return $this->authServices->coach_register($request);
    }

    public function change_password(Request $request)
    {
        return $this->authServices->change_password($request);
    }

    public function forget_password(Request $request)
    {
        return $this->authServices->forget_password($request);
    }

    public function update_version(Request $request)
    {
        return $this->authServices->update_version($request);
    }

}
