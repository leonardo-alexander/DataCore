<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile', 'verification');

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $request->validated();

        $user->update(collect($data)->only(['name', 'email'])->all());

        $profile = $user->profile()->firstOrCreate([]);

        $payload = collect($data)
            ->only(['phone_number', 'gender', 'dob', 'address'])
            ->map(fn ($value) => $value ?: null);

        if ($request->hasFile('image')) {
            if ($profile->picture_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $profile->picture_url));
            }

            $payload->put('picture_url', '/storage/' . $request->file('image')->store('avatars', 'public'));
        }

        $profile->update($payload->all());

        return back()->with('success', __('Your profile was updated.'));
    }
}
