<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    // =====================
    // SHOW BRANCH PAGE
    // =====================
    public function index()
    {
        $branches = Branch::all();

        return view('admin.branches', compact('branches'));
    }

    // =====================
    // STORE NEW BRANCH
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Branch::create([
            'name' => $request->name
        ]);

        return redirect('/admin/branches')->with('success', 'Branch added successfully!');
    }
}