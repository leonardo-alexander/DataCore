<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - DataCore</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#EEF2FF]">

    <div class="min-h-screen flex">

        <!-- LEFT SIDE (same as login) -->
        <div class="w-1/2 flex flex-col justify-center px-16 relative">

            <div class="flex flex-col items-start gap-2">
                <h1 class="text-5xl font-bold text-[#1E1B4B] leading-tight">
                    Welcome to
                </h1>

                <img src="{{ asset('images/datacore-logo.png') }}" class="h-32 w-auto" alt="DataCore Logo">
            </div>

            <p class="mt-8 text-gray-600 text-lg max-w-xl">
                your all in one platform to earn
                <span class="text-indigo-500 font-semibold">rewards</span>
                through surveys and trade high quality
                <span class="text-indigo-500 font-semibold">datasets</span> seamlessly.
            </p>

            <div
                class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-r from-indigo-200 to-purple-200 rounded-t-full opacity-30">
            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="w-1/2 bg-gradient-to-b from-indigo-600 to-indigo-800 flex items-center justify-center">

            <div class="w-80">

                <!-- Title -->
                <h2 class="text-3xl font-semibold text-white mb-6">
                    Register
                </h2>

                <!-- Form -->
                <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    <!-- Email -->
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    <!-- Password -->
                    <input type="password" name="password" placeholder="Password" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    <!-- Confirm Password -->
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    <!-- General error -->
                    @if ($errors->any())
                        <div class="text-red-200 bg-red-500/20 border border-red-300 rounded-lg px-4 py-3">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Button -->
                    <button type="submit"
                        class="w-full bg-white text-indigo-700 font-semibold py-3 rounded-lg hover:bg-gray-100 transition">
                        Register
                    </button>
                </form>

                <!-- Login link -->
                <p class="text-sm text-gray-200 mt-4">
                    Already have an account?
                    <a href="{{ route('login.view') }}" class="text-indigo-200 font-semibold">
                        Login now
                    </a>
                </p>

            </div>
        </div>

    </div>

</body>

</html>
