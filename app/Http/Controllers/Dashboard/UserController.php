<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role','user')->get();
        return view('Dashboard.User.index', compact('users'));
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
                'email' => $request->email,
                'role'    => 'user',
                'password'   => Hash::make($request->password),
            ]);

            return redirect()->route('users')->with('success', 'New user added successfully!');
        }
        return view('Dashboard.User.add');
    }

    public function edit(Request $request, $id)
    {
        $user = User::findorfail($id);
        if (!$user) {
            return back()->with('error', 'User Not Found!');
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('edit-user',$user->id)->with('success', 'User updated successfully!');
        }

        return view('Dashboard.User.edit', compact('user'));
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users')->with('error', 'User Not Found!');
        }
        $user->delete();
        return redirect()->route('users')->with('success', 'User removed successfully!');
    }
}
