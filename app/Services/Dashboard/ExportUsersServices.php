<?php

namespace App\Services\Dashboard;

use App\Exports\UsersExport;
use App\Services\ValidationServices;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportUsersServices
{
    public function __construct(protected ValidationServices $validationServices)
    {
    }

    public function exportUsersToExcel(Request $request)
    {
        $this->validationServices->exportUsersToExcel($request);
        return Excel::download(new UsersExport($request->export), 'users.xlsx');
    }
}
