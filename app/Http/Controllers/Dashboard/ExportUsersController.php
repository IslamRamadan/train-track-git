<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ExportUsersServices;
use Illuminate\Http\Request;

class ExportUsersController extends Controller
{
    public function __construct(protected ExportUsersServices $exportUsersServices)
    {
    }

    public function exportUsersToExcel(Request $request)
    {
        return $this->exportUsersServices->exportUsersToExcel($request);
    }
}
