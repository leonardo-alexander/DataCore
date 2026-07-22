@extends('layouts.guest')

@section('title', __('Create account'))

@section('content')
    <div class="lg:hidden mb-8 flex items-center gap-3">
        <div class="dc-spectrum flex h-10 w-10 items-center justify-center rounded-xl shadow-glow">
            <i data-lucide="hexagon" class="h-5 w-5 text-white"></i>
        </div>
        <span class="font-display text-lg font-semibold">DataCore</span>
    </div>

    <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900">{{ __('Create your account') }}</h1>
    <p class="mt-1.5 text-sm text-slate-500">{{ __('Start collecting and refining data in minutes.') }}</p>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5" x-data="{ show: false, termsOpen: false }">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Full name') }} <span
                    class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                placeholder="Rangga Aditya">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Email') }} <span
                    class="text-rose-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                placeholder="you@example.com">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Password') }} <span
                    class="text-rose-500">*</span></label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" required
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                    placeholder="{{ __('At least 8 characters') }}">
                <button type="button" @click="show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                    <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
                </button>
            </div>
        </div>
        <div x-data="{ show: false }">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Confirm password') }} <span
                    class="text-rose-500">*</span></label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                    placeholder="{{ __('Re-enter password') }}">
                <button type="button" @click="show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                    <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
                </button>
            </div>
        </div>
        <label class="flex items-start gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200">
            <span>
                {{ __('I agree to the') }}
                <button type="button" @click.stop="termsOpen = true" class="font-semibold text-indigo-600 underline hover:text-indigo-700">{{ __('Terms of Service and Privacy Policy') }}</button>.
                <span class="text-rose-500">*</span>
            </span>
        </label>
        <button type="submit"
            class="dc-spectrum inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
            {{ __('Create account') }}
        </button>
    </form>

    <div class="mt-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-slate-200"></div>
        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('or') }}</span>
        <div class="h-px flex-1 bg-slate-200"></div>
    </div>

    <a href="{{ route('auth.google') }}"
        class="mt-6 inline-flex w-full items-center justify-center gap-2.5 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
        <svg class="h-4.5 w-4.5" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
            <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
            <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
        </svg>
        {{ __('Continue with Google') }}
    </a>

    {{-- Terms of Service / Privacy Policy modal --}}
    <div x-show="termsOpen" x-cloak @keydown.escape.window="termsOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="termsOpen = false"></div>
        <div x-show="termsOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative flex max-h-[80vh] w-full max-w-lg flex-col rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="font-display text-base font-semibold text-slate-900">{{ __('Terms of Service and Privacy Policy') }}</h3>
                <button type="button" @click="termsOpen = false" class="text-slate-400 transition hover:text-slate-600">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <div class="space-y-4 overflow-y-auto px-6 py-5 text-sm leading-relaxed text-slate-600">
                <div>
                    <h4 class="font-semibold text-slate-900">{{ __('Terms of Service') }}</h4>
                    <p class="mt-1.5">{{ __('By creating a DataCore account you agree to use the marketplace, survey, and wallet features honestly: submitted survey entries must be your own genuine responses, and datasets you publish must be data you have the right to sell.') }}</p>
                    <p class="mt-2.5">{{ __('Entry rewards and dataset sale proceeds are credited to your in-app wallet and may be withdrawn to a linked bank account, subject to the minimum and processing times shown on the Wallet page. DataCore retains a platform fee on Full Clean refinement and on funded reward pools.') }}</p>
                    <p class="mt-2.5">{{ __('Accounts found submitting fraudulent survey entries, duplicate responses, or fake identity verification documents may be suspended and pending balances withheld.') }}</p>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900">{{ __('Privacy Policy') }}</h4>
                    <p class="mt-1.5">{{ __('We store the account details you provide (name, email, profile fields) and the identity verification documents you submit, used only to confirm you are a real, eligible respondent for reward-earning surveys.') }}</p>
                    <p class="mt-2.5">{{ __('Half Clean processing strips personally identifiable information from survey responses before a dataset is listed for sale; buyers never receive your raw, identifiable answers.') }}</p>
                    <p class="mt-2.5">{{ __('You may request a copy or deletion of your personal data at any time by contacting support from the account you registered with.') }}</p>
                </div>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 text-right">
                <button type="button" @click="termsOpen = false"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-slate-500">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Sign in') }}</a>
    </p>
@endsection
