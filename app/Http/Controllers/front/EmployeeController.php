<?php

namespace App\Http\Controllers\front;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeController extends Controller
{
    public function index()
    {
        $search = request()->input('search');
        $employees = Employee::employees()
            ->where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('website.employees.index', compact('employees', 'search'));
    }

    public function show($username)
    {
        $employee = Employee::where('user_name', $username)
            ->where('account_type', 'موظف')
            ->where('status', 1)
            ->firstOrFail();

        return view('website.employees.show', compact('employee'));
    }
}
