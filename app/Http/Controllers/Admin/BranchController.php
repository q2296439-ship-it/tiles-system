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
            'name' => 'required|unique:branches,name'
        ]);

        Branch::create([
            'name' => $request->name
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
            'name' => 'required'
        ]);

        $branch = Branch::findOrFail($id);

        $branch->update([
            'name' => $request->name
        ]);

        return redirect('/admin/branches')
            ->with('success', 'Branch updated successfully!');
    }

    // =====================
    // DELETE BRANCH
    // =====================
    public function delete($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->delete();

        return redirect('/admin/branches')
            ->with('success', 'Branch deleted successfully!');
    }
}