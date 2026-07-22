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
