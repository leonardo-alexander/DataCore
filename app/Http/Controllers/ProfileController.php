<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load('profile', 'verification');

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:Male,Female,Other,Prefer not to say'],
            'dob' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
            'address' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ], [
            'dob.before_or_equal' => 'You must be at least 17 years old.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        $profile = $user->profile()->firstOrCreate([]);
        $payload = [
            'phone_number' => $data['phone_number'] ?? null,
            'gender' => $data['gender'] ?? null,
            'dob' => $data['dob'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($profile->picture_url) {
                $oldPath = str_replace('/storage/', '', $profile->picture_url);
                Storage::disk('public')->delete($oldPath);
            }

            $payload['picture_url'] = '/storage/' . $request->file('image')->store('avatars', 'public');
        }

        $profile->update($payload);

        return back()->with('success', 'Your profile was updated.');
    }
}
