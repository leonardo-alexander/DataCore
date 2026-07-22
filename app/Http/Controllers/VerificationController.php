<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        $user         = Auth::user()->load('profile');
        $verification = $user->verification()->firstOrCreate([], ['status' => 'unverified']);

        return view('verification.index', compact('verification', 'user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_number'      => ['required', 'string', 'max:50'],
            'id_card'        => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'selfie'         => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'phone_number'   => ['nullable', 'string', 'max:20'],
            'gender'         => ['nullable', 'in:Male,Female,Other'],
            'dob'            => ['nullable', 'date', 'before:today', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'profession'     => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'in:Single,Married,Divorced,Widowed'],
        ], [
            'dob.before_or_equal' => 'You must be at least 17 years old.',
        ]);

        $user = Auth::user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            collect($data)->only(['phone_number', 'gender', 'dob', 'address', 'city', 'profession', 'marital_status'])->all()
        );

        $verification = $user->verification()->firstOrCreate([]);

        $verification->update([
            'id_number'  => $data['id_number'],
            'id_card_url' => '/storage/' . $request->file('id_card')->store('verifications', 'local'),
            'selfie_url'  => '/storage/' . $request->file('selfie')->store('verifications', 'local'),
            'status'      => 'pending',
            'note'        => null,
        ]);

        Activity::log(Auth::id(), 'system', 'Verification submitted', 'Your documents are under review.');

        return back()->with('success', 'Documents submitted. Verification usually completes within 24 hours.');
    }
}
