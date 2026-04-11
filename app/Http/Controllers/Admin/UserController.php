<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // 🔥 Load users with branch relation (ready for future list page)
        $users = User::with('branch')->get();

        // 🔥 Get all branches
        $branches = Branch::all();

        return view('admin.users', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        // 🔥 VALIDATION (UPDATED)
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'employee_name' => 'required|string|max:255',
            'employee_id' => 'required|string|max:255',
            'role' => 'required',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // 🔥 CREATE USER (UPDATED)
        User::create([
            'name' => $request->employee_name, // 🔥 gamitin natin real name dito
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_name' => $request->employee_name,
            'employee_id' => $request->employee_id,
            'role' => $request->role,
            'branch_id' => $request->branch_id,
        ]);

        // 🔥 REDIRECT WITH SUCCESS MESSAGE
        return redirect('/admin/users')->with('success', 'User added successfully!');
    }
}