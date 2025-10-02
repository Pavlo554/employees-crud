<?php

namespace App\Http\Controllers;
use App\Exports\EmployeesExport; 
use Maatwebsite\Excel\Facades\Excel; 


use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Log; 

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search');
        $sortBy = $request->input('sortBy', 'id');
        $sortDir = $request->input('sortDir', 'asc');

        $employees = Employee::query();

        if ($search) {
            $employees->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allowedSortColumns = [
            'id', 'first_name', 'last_name', 'email', 'phone',
            'position', 'salary', 'hired_at', 'status', 'updated_at'
        ];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        if (!in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $employees->orderBy($sortBy, $sortDir);

        $employees = $employees->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $employees->items(),
            'meta' => [
                'currentPage' => $employees->currentPage(),
                'perPage' => $employees->perPage(),
                'total' => $employees->total(),
                'lastPage' => $employees->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|min:2|max:100',
                'last_name' => 'required|string|min:2|max:100',
                'email' => 'required|email|unique:employees,email',
                'phone' => 'nullable|string|max:30',
                'position' => 'nullable|string|max:120',
                'salary' => 'required|numeric|min:0|decimal:0,2',
                'hired_at' => 'nullable|date',
                'status' => ['required', Rule::in(['active', 'inactive'])],
            ]);

            $employee = Employee::create($validated);

            return response()->json($employee, 201); // 201 Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the employee.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|min:2|max:100',
                'last_name' => 'required|string|min:2|max:100',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees')->ignore($employee->id), // Унікальність email, але ігноруємо поточний запис
                ],
                'phone' => 'nullable|string|max:30',
                'position' => 'nullable|string|max:120',
                'salary' => 'required|numeric|min:0|decimal:0,2',
                'hired_at' => 'nullable|date',
                'status' => ['required', Rule::in(['active', 'inactive'])],
            ]);

            $employee->update($validated);

            return response()->json($employee); // 200 OK
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating employee #' . $employee->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the employee.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
            return response()->json(null, 204); // 204 No Content
        } catch (\Exception $e) {
            Log::error('Error deleting employee #' . $employee->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the employee.'], 500);
        }
    }

    /**
     * Export employees to Excel.
     */
    public function export(Request $request)
    {
        // Отримання параметрів пошуку та сортування з запиту
        $search = $request->query('search');
        $sortColumn = $request->query('sort'); // Перевірте назву параметра у вашому API
        $sortDirection = $request->query('dir', 'asc'); // За замовчуванням 'asc'

        // Передаємо ці параметри в конструктор нашого класу експорту
        return Excel::download(
            new EmployeesExport($search, $sortColumn, $sortDirection),
            'employees.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}