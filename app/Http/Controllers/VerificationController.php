<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerificationRequest;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');

        $verification = $user->verification()->firstOrCreate([], ['status' => 'unverified']);

        return view('verification.index', compact('verification', 'user'));
    }

    public function store(StoreVerificationRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $autoVerify = config('datacore.auto_verify');

        $user->profile()->updateOrCreate(['user_id' => $user->id], $request->profileFields());

        $user->verification()->firstOrCreate([])->update([
            'id_number'   => $request->validated('id_number'),
            // Disk-relative paths on the private disk — these are ID photos, so they are
            // served only through the admin document route, never from a public URL.
            'id_card_url' => $request->file('id_card')->store('verifications', 'local'),
            'selfie_url'  => $request->file('selfie')->store('verifications', 'local'),
            'status'      => $autoVerify ? 'verified' : 'pending',
            'note'        => null,
        ]);

        if ($autoVerify) {
            Activity::log(
                $user->id,
                'system',
                __('Account verified'),
                __('Your identity is confirmed. You can now sell datasets.'),
            );

            return back()->with('success', __('Verification complete. You can now sell datasets.'));
        }

        Activity::log(
            $user->id,
            'system',
            __('Verification submitted'),
            __('Your documents are under review.'),
        );

        return back()->with('success', __('Documents submitted. Verification usually completes within 24 hours.'));
    }
}
