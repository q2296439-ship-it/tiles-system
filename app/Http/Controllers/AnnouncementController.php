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

        return view('announcements.index', compact('announcements'));
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