<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.index');
    }

    public function update(UpdateSettingsRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $updates = $request->preferences()
            ->put('currency', $request->validated('currency') ?? $user->currency);

        if (filled($request->validated('password'))) {
            $updates->put('password', Hash::make($request->validated('password')));
        }

        $user->update($updates->all());

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Settings saved.')]);
        }

        return redirect()->route('settings')->with('success', __('Settings saved.'));
    }
}
