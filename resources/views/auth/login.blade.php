<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - DataCore</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#EEF2FF]">

    <div class="min-h-screen flex">

        <!-- LEFT SIDE -->
        <div class="w-2/3 flex flex-col justify-center px-16 relative">

            <!-- Title -->
            <div class="flex flex-col items-start gap-2">
                <h1 class="text-5xl font-bold text-[#1E1B4B] leading-tight inline-block">
                    Welcome to
                </h1>

                <img src="{{ asset('images/datacore-logo.png') }}" class="h-32 w-auto inline-block" alt="DataCore Logo">
            </div>

            <!-- Tagline -->
            <p class="mt-8 text-gray-600 text-lg max-w-xl">
                your all in one platform to earn
                <span class="text-indigo-500 font-semibold">rewards</span>
                through surveys and trade high quality
                <span class="text-indigo-500 font-semibold">datasets</span> seamlessly.
            </p>

            <!-- Decorative wave -->
            <div
                class="absolute bottom-0 left-0 w-full h-32 bg-linear-to-r from-indigo-200 to-purple-200 rounded-t-full opacity-30">
            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="w-1/3 bg-linear-to-b from-indigo-600 to-indigo-800 flex items-center justify-center">

            <div class="w-80">

                <!-- Login title -->
                <h2 class="text-3xl font-semibold text-white mb-6">
                    Login
                </h2>

                <!-- Form -->
                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <input type="email" name="email" placeholder="Email" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    <!-- Password -->
                    <input type="password" name="password" placeholder="Password" required
                        class="w-full px-4 py-3 rounded-lg bg-indigo-500/40 text-white placeholder-gray-200 focus:outline-none focus:ring-2 focus:ring-white">

                    @if ($errors->any())
                        <div class="mb-4 text-red-200 bg-red-500/20 border border-red-300 rounded-lg px-4 py-3">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Button -->
                    <button type="submit"
                        class="w-full bg-white text-indigo-700 font-semibold py-3 rounded-lg hover:bg-gray-100 transition">
                        Login
                    </button>
                </form>

                <!-- Register -->
                <p class="text-sm text-gray-200 mt-4">
                    Don't have an account yet?
                    <a href="{{ route('register.view') }}" class="text-indigo-200 font-semibold">
                        Register now
                    </a>
                </p>

                {{-- <!-- Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-1 h-px bg-gray-300"></div>
                    <span class="px-3 text-gray-200 text-sm">or continue with</span>
                    <div class="flex-1 h-px bg-gray-300"></div>
                </div>

                <!-- Social -->
                <div class="flex justify-between">
                    <button class="bg-white p-3 rounded-lg w-14">G</button>
                    <button class="bg-white p-3 rounded-lg w-14"></button>
                    <button class="bg-white p-3 rounded-lg w-14">f</button>
                    <button class="bg-white p-3 rounded-lg w-14">t</button>
                </div> --}}

            </div>
        </div>

    </div>

</body>

</html>
