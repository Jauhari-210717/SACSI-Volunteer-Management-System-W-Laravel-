<?php

namespace App\Http\Controllers;

use App\Models\VolunteerProfile;
use Illuminate\Http\Request;

class VolunteerProfileController extends Controller
{
    /**
     * Display a listing of all volunteer profiles
     */
    public function index()
    {
        $volunteers = VolunteerProfile::all();
        return view('volunteers.index', compact('volunteers'));
    }

    /**
     * Show details of a single volunteer profile
     */
    public function show($id)
    {
        $volunteer = VolunteerProfile::findOrFail($id);
        return view('volunteers.show', compact('volunteer'));
    }

    /**
     * Update a volunteer profile
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:50', // New field
            'email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'fb_messenger' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:100',
            'year_level' => 'nullable|integer|min:1|max:10',
            'barangay' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'status' => 'nullable|in:Active,Inactive,Archived',
        ]);

        $volunteer = VolunteerProfile::findOrFail($id);
        $volunteer->update($request->only([
            'full_name',
            'id_number',
            'email',
            'contact_number',
            'emergency_contact',
            'fb_messenger',
            'course',
            'year_level',
            'barangay',
            'district',
            'status',
        ]));

        return redirect()->back()->with('success', 'Volunteer profile updated successfully!');
    }

    /**
     * Delete a volunteer profile
     */
    public function destroy($id)
    {
        $volunteer = VolunteerProfile::findOrFail($id);
        $volunteer->delete();

        return redirect()->back()->with('success', 'Volunteer profile deleted successfully!');
    }
}
