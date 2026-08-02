<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
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
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    private const WELCOME_BONUS = 50000;

    public function viewRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, WalletService $wallet)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);

            $this->setUpNewAccount($user);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $this->grantWelcomeBonus($user, $wallet);

        return redirect()->route('dashboard')->with('success', $this->welcomeMessage($user));
    }

    public function viewLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->credentials(), $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', __('Those credentials do not match our records.'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', __('Welcome back!'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('You have been signed out.'));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request, WalletService $wallet)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', __('Google sign-in failed. Please try again.'));
        }

        $user = User::where('google_id', $googleUser->getId())->first();
        $isNewAccount = false;

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $isNewAccount = true;

                $user = DB::transaction(function () use ($googleUser) {
                    $user = User::create([
                        'name'              => $googleUser->getName() ?: $googleUser->getNickname() ?: 'DataCore User',
                        'email'             => $googleUser->getEmail(),
                        'google_id'         => $googleUser->getId(),
                        'password'          => Hash::make(Str::random(40)),
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

            return redirect()->route('dashboard')->with('success', $this->welcomeMessage($user));
        }

        return redirect()->intended(route('dashboard'))->with('success', __('Welcome back!'));
    }

    private function setUpNewAccount(User $user, ?string $avatarUrl = null): void
    {
        Profile::create(['user_id' => $user->id, 'picture_url' => $avatarUrl]);
        Wallet::create(['user_id' => $user->id, 'balance' => 0]);
        Verification::create(['user_id' => $user->id, 'status' => 'unverified']);

        Activity::log(
            $user->id,
            'system',
            __('Welcome to DataCore'),
            __('Your account is ready. Verify your identity to start selling.'),
        );
    }

    private function grantWelcomeBonus(User $user, WalletService $wallet): void
    {
        $wallet->credit($user, self::WELCOME_BONUS, 'reward', [
            'description' => __('Welcome bonus'),
            'activity'    => __('You received a :amount welcome bonus', ['amount' => Money::format(self::WELCOME_BONUS)]),
        ]);
    }

    private function welcomeMessage(User $user): string
    {
        return __('Welcome to DataCore, :name! We dropped :amount in your wallet to get you started.', [
            'name'   => $user->name,
            'amount' => Money::format(self::WELCOME_BONUS),
        ]);
    }
}
