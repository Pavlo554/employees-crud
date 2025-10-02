<?php

namespace App\Exports;

use App\Models\Employee;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // Для заголовків
use Maatwebsite\Excel\Concerns\WithMapping;   // Для форматування даних
use Maatwebsite\Excel\Concerns\WithStyles;    // Для жирних заголовків
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Для стилів

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

        // Реалізація пошуку (search)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%'); // Додайте інші поля, якщо потрібно для пошуку
            });
        }

        // Реалізація сортування (sort/dir)
        if ($this->sortColumn) {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        } else {
            // За замовчуванням сортуємо за ID, або як вказано в ТЗ, якщо є
            $query->orderBy('id', 'asc');
        }

        // Вибір колонок у потрібному порядку
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

    // Метод для форматування даних перед записом у Excel
    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->position,
            number_format($employee->salary, 2, '.', ''), // Salary as number with 2 decimals
            $employee->hired_at ? \Carbon\Carbon::parse($employee->hired_at)->format('YYYY-MM-DD') : null, // Dates formatted YYYY-MM-DD
            $employee->status,
            $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('YYYY-MM-DD HH:mm:ss') : null,
        ];
    }

    // Метод для застосування стилів (жирні заголовки)
    public function styles(Worksheet $sheet)
    {
        return [
            // Застосувати жирний шрифт до першого рядка (заголовків)
            1    => ['font' => ['bold' => true]],
        ];
    }
}
