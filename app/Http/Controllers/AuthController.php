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
    // LOGIN FUNCTION (EMAIL BASED FIX)
    // =====================
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->username, // 🔥 gumagamit ng email kahit username field
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            // ADMIN
            if ($user->role === 'admin') {
                return redirect('/admin');
            }

            // CASHIER
            if ($user->role === 'cashier') {
                return redirect('/cashier?branch_id=' . $user->branch_id);
            }

            // INVENTORY
            if ($user->role === 'inventory') {
                return redirect('/inventory?branch_id=' . $user->branch_id);
            }
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