<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('role','employee')->get();
        return view('Dashboard.Employee.index', compact('employees'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email'      => 'required|string|email|max:255|unique:users,email',
                'password'   => 'required|string|min:8',
            ]);

            User::create([
                'name' => $request->name,
                'email'      => $request->email,
                'role'    => 'employee',
                'password'   => Hash::make($request->password),
            ]);
            return redirect()->route('employees')->with('success', 'New employee added successfully!');
        }

        return view('Dashboard.Employee.add');
    }

    public function edit(Request $request, $id)
    {
        $employee = User::findorfail($id);
        if (!$employee) {
            return back()->with('error', 'Employee Not Found!');
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email'      => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            ]);

            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('edit-employee',$employee->id)->with('success', 'Employee updated successfully!');
        }

        return view('Dashboard.Employee.edit', compact('employee'));
    }

    public function delete($id)
    {
        $employee = User::find($id);
        if (!$employee) {
            return redirect()->route('employees')->with('error', 'Employee Not Found!');
        }
        $employee->delete();
        return redirect()->route('employees')->with('success', 'Employee deleted successfully!');
    }
}
