<?php

namespace App\Exports;

use App\Models\Employee;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithMapping;   
use Maatwebsite\Excel\Concerns\WithStyles;    
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; 

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $sortColumn;
    protected $sortDirection;

    public function __construct($search = null, $sortColumn = null, $sortDirection = 'asc')
    {
        $this->search = $search;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
    }

    public function collection()
    {
        $query = Employee::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%'); 
            });
        }

        if ($this->sortColumn) {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        } else {
            $query->orderBy('id', 'asc');
        }

        return $query->select(
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'position',
            'salary',
            'hired_at',
            'status',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Position',
            'Salary',
            'Hired At',
            'Status',
            'Updated At',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->position,
            number_format($employee->salary, 2, '.', ''), 
            $employee->hired_at ? \Carbon\Carbon::parse($employee->hired_at)->format('YYYY-MM-DD') : null, 
            $employee->status,
            $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('YYYY-MM-DD HH:mm:ss') : null,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
