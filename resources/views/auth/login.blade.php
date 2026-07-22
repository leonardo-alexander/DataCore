@extends('layouts.guest')

@section('title', __('Sign in'))

@section('content')
    <div class="lg:hidden mb-8 flex items-center gap-3">
        <div class="dc-spectrum flex h-10 w-10 items-center justify-center rounded-xl shadow-glow">
            <i data-lucide="hexagon" class="h-5 w-5 text-white"></i>
        </div>
        <span class="font-display text-lg font-semibold">DataCore</span>
    </div>

    <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900">{{ __('Welcome back') }}</h1>
    <p class="mt-1.5 text-sm text-slate-500">{{ __('Sign in to keep refining your datasets.') }}</p>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" x-data="{ show: false }">
        @csrf
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Email') }} <span class="text-rose-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                   placeholder="you@example.com">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Password') }} <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" required
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                       placeholder="••••••••">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                    <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
                </button>
            </div>
        </div>
        <label class="flex items-center gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200">
            {{ __('Remember me') }}
        </label>
        <button type="submit" class="dc-spectrum inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
            <i data-lucide="log-in" class="h-4 w-4"></i> {{ __('Sign in') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        {{ __('New to DataCore?') }}
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Create an account') }}</a>
    </p>
@endsection
