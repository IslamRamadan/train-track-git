<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LogServices;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(protected LogServices $logServices)
    {
    }

    public function client_logs_list(Request $request)
    {
        return $this->logServices->client_logs_list($request);
    }
    public function client_programs_logs_list(Request $request)
    {
        return $this->logServices->client_programs_logs_list($request);
    }
}
