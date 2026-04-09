<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // 🔥 VALIDATION
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // 🔥 EMAIL LOGIN (FIXED: trim + lowercase)
        $credentials = [
            'email' => strtolower(trim($request->username)),
            'password' => $request->password
        ];

        // 🔥 REMEMBER ME SUPPORT
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {

            $request->session()->regenerate();

            $user = Auth::user();

            // =====================
            // ROLE BASED REDIRECT
            // =====================

            // ADMIN
            if (strtolower(trim($user->role)) === 'admin') {
                return redirect('/admin');
            }

            // CASHIER
            if (strtolower(trim($user->role)) === 'cashier') {
                return redirect('/cashier');
            }

            // INVENTORY
            if (strtolower(trim($user->role)) === 'inventory') {
                return redirect('/inventory-dashboard');
            }

            // 🔥 BRANCH MANAGER (FIXED)
            if (strtolower(trim($user->role)) === 'manager') {
                return redirect('/admin/manager/approvals');
            }

            // DEFAULT FALLBACK
            return redirect('/');
        }

        return back()->with('error', 'Invalid login credentials');
    }

    // =====================
    // LOGOUT (SECURE FIX)
    // =====================
    public function logout(Request $request)
    {
        Auth::logout();

        // 🔥 IMPORTANT SECURITY
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}