<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =====================
    // SHOW LOGIN PAGE
    // =====================
    public function showLogin()
    {
        return view('login');
    }

    // =====================
    // LOGIN FUNCTION (EMAIL BASED FIX - FINAL)
    // =====================
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'email' => strtolower(trim($request->username)),
            'password' => $request->password
        ];

        $remember = $request->has('remember');

        $userCheck = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$userCheck) {
            return back()->withErrors(['login' => 'User not found (email mismatch)']);
        }

        if (Auth::attempt($credentials, $remember)) {

            $request->session()->regenerate();

            $user = Auth::user();
            $role = strtolower(trim($user->role));

            if ($role === 'admin') {
                return redirect('/admin');
            }

            if ($role === 'cashier') {
                return redirect('/cashier');
            }

            if ($role === 'inventory') {
                return redirect('/inventory-dashboard');
            }

            if ($role === 'manager' || $role === 'branch_manager') {
                return redirect('/manager');
            }

            return redirect('/');
        }

        return back()->withErrors(['login' => 'Wrong password']);
    }

    // =====================
    // SHOW CHANGE PASSWORD PAGE
    // =====================
    public function showChangePassword()
    {
        return view('cashier.change-password');
    }

    // =====================
    // CHANGE PASSWORD FUNCTION
    // =====================
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password changed successfully!');
    }

    // =====================
    // LOGOUT (SECURE FIX)
    // =====================
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}