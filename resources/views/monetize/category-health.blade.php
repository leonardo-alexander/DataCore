@extends('layouts.app')

@section('title', 'Health - DataCore')

@section('content')

<div class="min-h-screen">

    <!-- SIDEBAR -->
    @include('components.sidebar')

    <!-- MAIN -->
    <main class="ml-64">

        <!-- TOP SECTION -->
        <section class="bg-gray-100 px-10 py-10 text-center">

            <div class="flex justify-end mb-6">
                <input type="text" placeholder="Search..."
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white placeholder-gray-200 w-64 focus:outline-none">
            </div>

            <h1 class="text-4xl font-extrabold text-gray-800">
                HEALTH
            </h1>

            <p class="text-gray-500 mt-2 text-sm max-w-2xl mx-auto">
                HIGH-QUALITY MEDICAL DATASETS, GENOMICS, HEALTHCARE ANALYTICS,
                AND CLINICAL RESEARCH DATA FOR ADVANCING HEALTH SYSTEMS.
            </p>

            <!-- CATEGORY CARDS -->
            <div class="grid grid-cols-3 gap-6 mt-8">

                <div class="rounded-2xl overflow-hidden shadow hover:scale-105 transition">
                    <img src="{{ asset('images/genomics.png') }}" class="w-full h-40 object-cover">
                    <p class="mt-2 text-sm font-semibold text-gray-700">GENOMICS</p>
                </div>

                <div class="rounded-2xl overflow-hidden shadow hover:scale-105 transition">
                    <img src="{{ asset('images/clinical.png') }}" class="w-full h-40 object-cover">
                    <p class="mt-2 text-sm font-semibold text-gray-700">CLINICAL TRIALS</p>
                </div>

                <div class="rounded-2xl overflow-hidden shadow hover:scale-105 transition">
                    <img src="{{ asset('images/imaging.png') }}" class="w-full h-40 object-cover">
                    <p class="mt-2 text-sm font-semibold text-gray-700">MEDICAL IMAGING</p>
                </div>

            </div>

        </section>

        <!-- DATASET SECTION -->
        <section class="bg-gradient-to-br from-indigo-600 to-purple-700 px-10 py-10">

            <h2 class="text-white text-xl font-bold mb-6">
                All Datasets
            </h2>

            <div class="grid grid-cols-4 gap-6">

                @for ($i = 0; $i < 12; $i++)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:scale-105 transition">

                    <!-- IMAGE -->
                    <div class="relative">
                        <img src="{{ asset('images/dataset-card.png') }}" class="w-full h-40 object-cover">

                        <!-- BADGE -->
                        <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded">
                            30% OFF
                        </span>
                    </div>

                    <!-- CONTENT -->
                    <div class="p-4">

                        <h3 class="text-sm font-semibold text-gray-700">
                            COVID-19 Variant Tracking
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Anonymized Clinical Data With Tracking Models for Predictive Modeling.
                        </p>

                        <div class="mt-3 text-sm font-semibold text-indigo-600">
                            50 Point
                        </div>

                    </div>

                </div>
                @endfor

            </div>

        </section>

    </main>

</div>

@endsection