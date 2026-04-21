<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->get();

        $role = strtolower(auth()->user()->role ?? '');

        if ($role === 'manager') {
            $layout = 'layouts.manager';
        } else {
            $layout = 'layouts.admin';
        }

        return view('announcements.index', compact(
            'announcements',
            'layout'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'message' => 'required',
        ]);

        Announcement::create([
            'title'      => $request->title,
            'message'    => $request->message,
            'created_by' => Auth::id(),
            'is_active'  => true,
        ]);

        $old = Announcement::latest()
            ->skip(10)
            ->get();

        foreach ($old as $row) {
            $row->delete();
        }

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        $announcement->update([
            'is_active' => false
        ]);

        return back()->with('success', 'Announcement removed.');
    }
}