@extends('settings.layout')

@section('settings-content')
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- PROFILE CARD -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-6">

            <!-- Avatar -->
            <div class="relative">
                <img src="https://ui-avatars.com/api/?name=Nyoney+Naqale" class="w-20 h-20 rounded-full object-cover">

                <button class="absolute bottom-0 right-0 bg-white p-1.5 rounded-full shadow border">
                    <x-heroicon-o-camera class="w-4 h-4 text-gray-600" />
                </button>
            </div>

            <!-- Info -->
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800">
                    Nyoney Naqale
                </h3>

                <p class="text-sm text-gray-500">
                    nyoneynaqale7@email.com
                </p>

                <p class="text-sm text-gray-400 mt-1">
                    +62 812-3456-7890
                </p>

                <div class="flex items-center gap-3 mt-2">
                    <span
                        class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1 rounded-full bg-orange-100 text-orange-600">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                        Not Verified
                    </span>

                    <button
                        class="text-xs font-medium px-3 py-1 rounded-full bg-primary text-white hover:opacity-90 transition">
                        Verify Email
                    </button>
                </div>
            </div>

        </div>

        <!-- FORM -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">

            <h2 class="text-lg font-semibold text-gray-800 mb-6">
                Personal Information
            </h2>

            <form class="space-y-6">

                <!-- NAME -->
                <div>
                    <label class="label">Full Name</label>
                    <input type="text" value="Nyoney Naqale" class="input mt-1">
                </div>

                <!-- PHONE -->
                <div>
                    <label class="label">Phone Number</label>
                    <input type="text" value="+62 812-3456-7890" class="input mt-1">
                </div>

                <!-- GRID -->
                <div class="grid grid-cols-2 gap-5">    

                    <!-- GENDER -->
                    <div>
                        <label class="label">Gender</label>

                        <div class="relative mt-1">
                            <select name="gender" class="input pr-10 appearance-none">
                                <option value="male">Male</option>
                                <option value="female" selected>Female</option>
                            </select>

                            <x-heroicon-o-chevron-down
                                class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        </div>
                    </div>

                    <!-- DOB -->
                    <div>
                        <label class="label">Date of Birth</label>
                        <input type="date" value="2000-05-15" class="input mt-1">
                    </div>

                </div>

                <!-- ADDRESS -->
                <div>
                    <label class="label">Address</label>
                    <textarea rows="3" class="input mt-1 resize-none">Jakarta, Indonesia</textarea>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="label">Email</label>

                    <div class="relative mt-1">
                        <input type="email" value="nyoneynaqale7@email.com" disabled class="input input-disabled pr-10">

                        <x-heroicon-o-lock-closed class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="pt-4 flex justify-end">
                    <button type="button"
                        class="bg-primary text-white px-6 py-3 rounded-xl font-medium
                   shadow-sm hover:shadow-md hover:opacity-95 transition">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>

    </div>
@endsection
