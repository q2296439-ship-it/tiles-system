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
        $branches = Branch::latest()->get(); // 🔥 latest first

        return view('admin.branches', compact('branches'));
    }

    // =====================
    // STORE NEW BRANCH
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:branches,name',
            'address' => 'nullable|string|max:255'
        ]);

        Branch::create([
            'name' => $request->name,
            'address' => $request->address // 🔥 ADD
        ]);

        return redirect('/admin/branches')
            ->with('success', 'Branch added successfully!');
    }

    // =====================
    // UPDATE BRANCH
    // =====================
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255'
        ]);

        $branch = Branch::findOrFail($id);

        $branch->update([
            'name' => $request->name,
            'address' => $request->address // 🔥 ADD
        ]);

        // 🔥 IMPORTANT FIX (para bumalik sa manage page)
        return redirect('/admin/manage')
            ->with('success', 'Branch updated successfully!');
    }

    // =====================
    // DELETE BRANCH
    // =====================
    public function delete($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->delete();

        // 🔥 IMPORTANT FIX
        return redirect('/admin/manage')
            ->with('success', 'Branch deleted successfully!');
    }
}