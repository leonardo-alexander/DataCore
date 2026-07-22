<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'currency'         => ['nullable', 'in:IDR,USD'],
            'password'         => ['nullable', 'required_with:password_confirmation', 'string', 'min:8', 'confirmed'],
            'notify_sales'     => ['nullable', 'boolean'],
            'notify_rewards'   => ['nullable', 'boolean'],
            'notify_cleaning'  => ['nullable', 'boolean'],
            'notify_marketing' => ['nullable', 'boolean'],
        ]);

        $updates = ['currency' => $data['currency'] ?? $user->currency];

        foreach (['notify_sales', 'notify_rewards', 'notify_cleaning', 'notify_marketing'] as $pref) {
            if (array_key_exists($pref, $data) && $data[$pref] !== null) {
                $updates[$pref] = (bool) $data[$pref];
            }
        }

        $user->update($updates);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Settings saved.')]);
        }

        return redirect()->route('settings')
            ->with('success', __('Settings saved.'));
    }
}
