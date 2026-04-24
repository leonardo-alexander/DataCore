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
                <a href="{{ route('profile.view') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('profile.view') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Profile

                    @if (request()->routeIs('profile.view'))
                        <span class="absolute left-0 bottom-0 w-full h-0.5 bg-white rounded"></span>
                    @endif
                </a>

                <a href="{{ route('security.view') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('security.view') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Security

                    @if (request()->routeIs('security.view'))
                        <span class="absolute left-0 bottom-0 w-full h-0.5 bg-white rounded"></span>
                    @endif
                </a>

                <a href="{{ route('activity.view') }}"
                    class="pb-3 relative transition
                    {{ request()->routeIs('activity.view') ? 'text-white font-medium' : 'text-white/60 hover:text-white' }}">

                    Activity

                    @if (request()->routeIs('activity.view'))
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
