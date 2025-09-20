<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role','admin')->get();
        return view('Dashboard.Admin.index', compact('admins'));
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
                'role'    => 'admin',
                'password'   => Hash::make($request->password),
            ]);
            return redirect()->route('admins')->with('success', 'New admin added successfully!');
        }

        return view('Dashboard.Admin.add');
    }

    public function edit(Request $request, $id)
    {
        $admin = User::findorfail($id);
        if (!$admin) {
            return back()->with('error', 'Admin Not Found!');
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email'      => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            ]);

            $admin->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('edit-admin',$admin->id)->with('success', 'Admin updated successfully!');
        }

        return view('Dashboard.Admin.edit', compact('admin'));
    }

    public function delete($id)
    {
        $admin = User::find($id);
        if (!$admin) {
            return redirect()->route('admins')->with('error', 'Admin Not Found!');
        }
        $admin->delete();
        return redirect()->route('admins')->with('success', 'Admin deleted successfully!');
    }
}
