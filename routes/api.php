<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController; // Важливо імпортувати контролер


// Маршрут для користувача, захищений Sanctum (якщо використовується)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Додатковий маршрут для експорту - ПЕРЕМІЩЕНО ВГОРУ!
// Цей маршрут є більш специфічним і повинен бути оголошений перед Route::apiResource.
Route::get('/employees/export', [EmployeeController::class, 'export']);

// Маршрути для Employee CRUD (загальніші маршрути ресурсів)
Route::apiResource('employees', EmployeeController::class);

// Додатковий маршрут для масового видалення (якщо буде реалізовано)
// Якщо ви плануєте його використовувати, він також повинен бути вище apiResource
// Route::delete('employees', [EmployeeController::class, 'bulkDelete']);