<aside class="w-64 h-screen fixed top-0 left-0 bg-white border-r border-gray-100 flex flex-col justify-between">

    <!-- TOP -->
    <div>

        <!-- Logo -->
        <div class="px-6 py-6">
            <img src="{{ asset('images/datacore-logo.png') }}" class="h-10" alt="DataCore">
        </div>

        <!-- DASHBOARD -->
        <div class="px-4 mb-6">
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-primary text-white font-medium shadow-sm">
                <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                Dashboard
            </a>
        </div>

        <!-- NAV -->
        <nav class="px-4 space-y-6">

            <!-- DATA -->
            <div>
                <p class="text-xs font-semibold text-gray-400 px-3 mb-2 uppercase tracking-wider">
                    Data
                </p>

                <div class="space-y-1">

                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        Browse Datasets
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                        <x-heroicon-o-circle-stack class="w-5 h-5" />
                        My Datasets
                    </a>

                </div>
            </div>

            <!-- SURVEYS -->
            <div>
                <p class="text-xs font-semibold text-gray-400 px-3 mb-2 uppercase tracking-wider">
                    Surveys
                </p>

                <div class="space-y-1">

                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        Browse Surveys
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                        My Surveys
                    </a>

                </div>
            </div>

            <!-- SYSTEM -->
            <div>
                <p class="text-xs font-semibold text-gray-400 px-3 mb-2 uppercase tracking-wider">
                    System
                </p>

                <div class="space-y-1">

                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                        Settings
                    </a>

                </div>
            </div>

        </nav>
    </div>

    <!-- BOTTOM -->
    <div class="px-4 pb-6">
        <a href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-500 hover:bg-red-50 transition">
            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
            Log Out
        </a>
    </div>

</aside>
