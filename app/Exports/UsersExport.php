<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected $export)
    {
    }

    /**
     * Fetch users' data
     */
    public function collection()
    {
        return User::select('name', 'email', 'phone', 'user_type')
            ->whereIn('user_type', $this->export)
            ->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return ["Name", "Email", "Phone", "Type"];
    }

    /**
     * Map data before exporting
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
            $row->phone,
            $row->user_type == "0" ? 'Coach' : 'Client' // Convert user_type to text
        ];
    }
}
