<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');
    }

    public function showPhotoForm()
    {
        return view('profile.photo');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        
        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile_photos/' . $user->profile_photo);
        }

        // Store new photo
        $file = $request->file('profile_photo');
        $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
        $file->storeAs('profile_photos', $filename, 'public');

        // Update user record
        $user->profile_photo = $filename;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Foto profil berhasil diperbarui!');
    }

    public function deletePhoto()
    {
        $user = Auth::user();
        
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile_photos/' . $user->profile_photo);
            $user->profile_photo = null;
            $user->save();
        }

        return redirect()->route('profile.show')->with('success', 'Foto profil berhasil dihapus!');
    }
}
