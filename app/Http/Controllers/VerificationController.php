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

        $user->profile()->updateOrCreate(['user_id' => $user->id], $request->profileFields());

        $user->verification()->firstOrCreate([])->update([
            'id_number'   => $request->validated('id_number'),
            'id_card_url' => '/storage/' . $request->file('id_card')->store('verifications', 'local'),
            'selfie_url'  => '/storage/' . $request->file('selfie')->store('verifications', 'local'),
            'status'      => 'pending',
            'note'        => null,
        ]);

        Activity::log(
            $user->id,
            'system',
            __('Verification submitted'),
            __('Your documents are under review.'),
        );

        return back()->with('success', __('Documents submitted. Verification usually completes within 24 hours.'));
    }
}
