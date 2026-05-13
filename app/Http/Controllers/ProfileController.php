<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $profiles = Profile::all();

        return view('profile.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('profile.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate
        $request->validate([
            'picture_url' => 'nullable',
            'phone_number' => 'nullable',
            'gender' => 'nullable',
            'dob' => 'nullable',
            'address' => 'nullable',
        ]);

        $path = null;

        if($request->hasFile('picture_url')){
            $path = $request->file('picture_url')
                            ->store('profiles', 'public');
        }

        // create data
        Profile::create([
            'user_id' => Auth::id(),
            'picture_url' => $path,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'address' => $request->address,
        ]);

        //redirect
        return redirect('/profile');
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        return view('profile.show', compact('profile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        return view('profile.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $request->validate([
            'phone_number' => 'required',
            'gender' => 'required'
        ]);

        $profile->update([
            'phone_number' => $request->phone_number,
            'gender' => $request->gender
        ]);

        return redirect('/profile');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $profile->delete();
        return redirect('/profile');
    }
}
