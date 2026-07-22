<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Profile;
use App\Models\User;
use App\Models\Verification;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

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

            $this->setUpNewAccount($user);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $this->grantWelcomeBonus($user, $wallet);

        return redirect()->route('dashboard')->with('success', 'Welcome to DataCore, ' . $user->name . '! We dropped ' . Money::format(50000) . ' in your wallet to get you started.');
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

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request, WalletService $wallet)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();
        $isNewAccount = false;

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Existing email/password account signing in with Google for the first time: link it.
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $isNewAccount = true;

                $user = DB::transaction(function () use ($googleUser) {
                    $user = User::create([
                        'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'DataCore User',
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'password' => Hash::make(Str::random(40)),
                        'email_verified_at' => now(),
                    ]);

                    $this->setUpNewAccount($user, $googleUser->getAvatar());

                    return $user;
                });
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($isNewAccount) {
            $this->grantWelcomeBonus($user, $wallet);

            return redirect()->route('dashboard')->with('success', 'Welcome to DataCore, ' . $user->name . '! We dropped ' . Money::format(50000) . ' in your wallet to get you started.');
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
    }

    /**
     * Shared setup for a brand-new account, regardless of how it was created.
     */
    private function setUpNewAccount(User $user, ?string $avatarUrl = null): void
    {
        Profile::create(['user_id' => $user->id, 'picture_url' => $avatarUrl]);
        Wallet::create(['user_id' => $user->id, 'balance' => 0]);
        Verification::create(['user_id' => $user->id, 'status' => 'unverified']);
        Activity::log($user->id, 'system', 'Welcome to DataCore', 'Your account is ready. Verify your identity to start selling.');
    }

    private function grantWelcomeBonus(User $user, WalletService $wallet): void
    {
        $wallet->credit($user, 50000, 'reward', [
            'description' => 'Welcome bonus',
            'activity' => 'You received a ' . Money::format(50000) . ' welcome bonus',
        ]);
    }
}
