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
        // 🔥 Load users with branch relation
        $users = User::with('branch')->get();

        // 🔥 Get all branches
        $branches = Branch::all();

        return view('admin.users', compact('users', 'branches'));
    }

    public function store(Request $request)
    {
        // 🔥 VALIDATION (IMPORTANT)
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role' => 'required',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // 🔥 CREATE USER (FIXED NAME ERROR)
        User::create([
            'name' => $request->username, // 🔥 FIX (important)
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
        ]);

        // 🔥 REDIRECT WITH SUCCESS MESSAGE
        return redirect('/admin/users')->with('success', 'User added successfully!');
    }
}