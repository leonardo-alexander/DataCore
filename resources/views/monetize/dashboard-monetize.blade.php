@extends('layouts.app')

@section('title', 'Dashboard - DataCore')

@section('content')

<div class="min-h-screen flex bg-gradient-to-b from-[#4536C5] via-[#584FA7] to-[#211A5F]">

    <!-- SIDEBAR -->
    @include('components.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-64 overflow-x-hidden">

        <!-- TOP BAR -->
        <div class="flex justify-end px-10 pt-8">
            <div class="relative">
                <input
                    type="text"
                    placeholder="Search"
                    class="w-64 rounded-full border-0 bg-white px-5 py-2 text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-400">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 absolute left-4 top-3 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- HERO -->
        <section class="px-10 pt-8 pb-14 text-center bg-white mx-8 rounded-t-3xl shadow-lg">

            <h1 class="text-5xl font-black text-gray-800 tracking-wide">
                DATASET OF THE MONTH
            </h1>

            <p class="text-gray-500 mt-3 text-sm tracking-widest">
                INSIGHTS —
                <span class="text-indigo-600 font-bold">2026 TRENDS</span>
                — RAW DATA — CLEANED —
            </p>

            <div class="flex justify-center mt-10">
                <img src="{{ asset('images/img_dashboard_monetize.png') }}"
                    class="h-72 drop-shadow-2xl hover:scale-105 transition duration-300">
            </div>

            <p class="text-indigo-600 font-bold mt-6 tracking-widest">
                2026 TRENDS
            </p>

        </section>

        <!-- CATEGORIES -->
        <section class="bg-gradient-to-r from-[#4F46E5] to-[#6D5DFD] py-12 px-10 mx-8">

            <h2 class="text-2xl font-black text-white mb-8 tracking-wide">
                CATEGORIES
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- CARD -->
                <div class="bg-white rounded-3xl p-8 text-center shadow-xl hover:-translate-y-2 hover:scale-105 transition duration-300 cursor-pointer">

                    <img src="{{ asset('images/health.png') }}"
                        class="h-24 mx-auto mb-5">

                    <p class="font-bold text-gray-700 tracking-wide">
                        HEALTH
                    </p>

                </div>

                <div class="bg-white rounded-3xl p-8 text-center shadow-xl hover:-translate-y-2 hover:scale-105 transition duration-300 cursor-pointer">

                    <img src="{{ asset('images/education.png') }}"
                        class="h-24 mx-auto mb-5">

                    <p class="font-bold text-gray-700 tracking-wide">
                        EDUCATION
                    </p>

                </div>

                <div class="bg-white rounded-3xl p-8 text-center shadow-xl hover:-translate-y-2 hover:scale-105 transition duration-300 cursor-pointer">

                    <img src="{{ asset('images/technology.png') }}"
                        class="h-24 mx-auto mb-5">

                    <p class="font-bold text-gray-700 tracking-wide">
                        TECHNOLOGY
                    </p>

                </div>

            </div>

            <p class="text-center text-xs text-white/80 mt-8 tracking-widest">
                CHECK OUT ALL THE CATEGORIES — VIEW ALL
            </p>

        </section>

        <!-- RECENT DATASETS -->
        <section class="bg-white py-12 px-10 mx-8">

            <h2 class="text-2xl font-black text-gray-700 mb-8 tracking-wide">
                RECENTLY ADDED DATASETS
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                @for ($i = 0; $i < 3; $i++)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-lg overflow-hidden hover:-translate-y-2 hover:shadow-2xl transition duration-300">

                    <img src="{{ asset('images/dataset-card.png') }}"
                        class="w-full h-52 object-cover">

                    <div class="p-5">

                        <h3 class="font-bold text-gray-800">
                            Econ Sentiment Data
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                            Cleaned · Structured · Ready for Machine Learning
                        </p>

                        <div class="flex justify-between items-center mt-5">

                            <span class="text-pink-500 font-bold text-sm">
                                +40 Coins
                            </span>

                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-full transition">
                                View
                            </button>

                        </div>

                    </div>

                </div>
                @endfor

            </div>

            <p class="text-center text-xs text-gray-400 mt-8 tracking-widest">
                CHECK OUT ALL THE RECENTLY ADD — VIEW ALL
            </p>

        </section>

        <!-- MOST DOWNLOADED -->
        <section class="bg-gradient-to-r from-[#4F46E5] to-[#6D5DFD] py-12 px-10 mx-8">

            <h2 class="text-2xl font-black text-white mb-8 tracking-wide">
                MOST DOWNLOADED ASSETS
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                @for ($i = 0; $i < 3; $i++)
                <div class="bg-white rounded-3xl overflow-hidden shadow-xl hover:scale-105 hover:-translate-y-2 transition duration-300 cursor-pointer">

                    <img src="{{ asset('images/chart.png') }}"
                        class="w-full h-52 object-cover">

                    <div class="p-5">

                        <h3 class="font-bold text-gray-800">
                            Market Research 2026
                        </h3>

                        <p class="text-sm text-gray-500 mt-2">
                            High demand dataset with premium analytics
                        </p>

                        <div class="mt-5 flex justify-between items-center">

                            <span class="text-green-500 font-bold text-sm">
                                BEST SELLER
                            </span>

                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-full transition">
                                Download
                            </button>

                        </div>

                    </div>

                </div>
                @endfor

            </div>

            <p class="text-center text-xs text-white/80 mt-8 tracking-widest">
                CHECK OUT ALL THE ASSETS — VIEW ALL
            </p>

        </section>

        <!-- FOOTER -->
        <section class="bg-white py-14 text-center mx-8 rounded-b-3xl shadow-lg mb-8">

            <img src="{{ asset('images/datacore-logo.png') }}"
                class="h-20 mx-auto mb-5">

            <p class="text-gray-400 text-sm tracking-wide">
                Start Selling with Us. Your Platform for Success!
            </p>

        </section>

    </main>

</div>

@endsection