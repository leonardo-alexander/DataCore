<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Profile;
use App\Models\User;
use App\Models\Verification;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function viewRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, WalletService $wallet)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            Profile::create(['user_id' => $user->id]);
            Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            Verification::create(['user_id' => $user->id, 'status' => 'unverified']);
            Activity::log($user->id, 'system', 'Welcome to DataCore', 'Your account is ready. Verify your identity to start selling.');

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $wallet->credit($user, 50000, 'reward', [
            'description' => 'Welcome bonus',
            'activity' => 'You received a ' . \App\Support\Money::format(50000) . ' welcome bonus',
        ]);

        return redirect()->route('dashboard')->with('success', 'Welcome to DataCore, ' . $user->name . '! We dropped ' . \App\Support\Money::format(50000) . ' in your wallet to get you started.');
    }

    public function viewLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Those credentials do not match our records.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }
}
