@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="flex min-h-screen bg-background">

        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="ml-64 flex-1 h-screen overflow-y-auto bg-linear-to-br from-primary to-primary-dark text-white p-8">

            <!-- HEADER -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-white">Settings</h1>
            </div>

            <!-- Tabs -->
            <div class="flex gap-6 border-b border-white/20 mb-8 text-sm">

                <a href="{{ route('settings.profile') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('settings.profile') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Profile

                    @if (request()->routeIs('settings.profile'))
                        <span class="absolute left-0 bottom-0 w-full h-0.5 bg-white rounded"></span>
                    @endif
                </a>

                <a href="{{ route('settings.security') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('settings.security') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Security

                    @if (request()->routeIs('settings.security'))
                        <span class="absolute left-0 bottom-0 w-full h-0.5 bg-white rounded"></span>
                    @endif
                </a>

                <a href="{{ route('settings.activity') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('settings.activity') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Activity

                    @if (request()->routeIs('settings.activity'))
                        <span class="absolute left-0 bottom-0 w-full h-0.5 bg-white rounded"></span>
                    @endif
                </a>

            </div>

            <!-- Content -->
            <div class="bg-white text-gray-800 rounded-2xl p-6 shadow-lg">
                @yield('settings-content')
            </div>

        </div>

    </div>
@endsection
