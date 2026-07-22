@extends('layouts.app')

@section('title', 'Verification')

@section('content')
    @php
        $status = $verification->status ?? 'unverified';
        $states = [
            'unverified' => [
                'label' => 'Not verified',
                'icon' => 'shield',
                'wrap' => 'border-slate-200 bg-slate-50',
                'tile' => 'bg-slate-400',
                'text' => 'text-slate-900',
                'sub' => 'text-slate-500',
                'msg' => 'Verify your identity to unlock selling, payouts, and a trust badge on your datasets.',
            ],
            'pending' => [
                'label' => 'Under review',
                'icon' => 'clock',
                'wrap' => 'border-amber-200 bg-amber-50',
                'tile' => 'bg-amber-500',
                'text' => 'text-amber-900',
                'sub' => 'text-amber-700',
                'msg' => 'We received your documents. Review usually takes less than 24 hours.',
            ],
            'verified' => [
                'label' => 'Verified',
                'icon' => 'badge-check',
                'wrap' => 'border-emerald-200 bg-emerald-50',
                'tile' => 'bg-emerald-500',
                'text' => 'text-emerald-900',
                'sub' => 'text-emerald-700',
                'msg' => 'Your identity is confirmed. You can sell datasets and withdraw earnings.',
            ],
            'rejected' => [
                'label' => 'Action needed',
                'icon' => 'alert-triangle',
                'wrap' => 'border-rose-200 bg-rose-50',
                'tile' => 'bg-rose-500',
                'text' => 'text-rose-900',
                'sub' => 'text-rose-700',
                'msg' =>
                    $verification->note ?:
                    'We could not read your documents clearly. Please re-upload using the tips on the right.',
            ],
        ];
        $s = $states[$status] ?? $states['unverified'];
        $showForm = in_array($status, ['unverified', 'rejected'], true);
        $profile = $user->profile;

        $pval = fn($field) => old($field, $profile?->{$field} ?? '');
    @endphp

    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">Verification</h1>
        <p class="mt-1 text-sm text-slate-500">Confirm your identity to build trust and start selling on DataCore.</p>
    </div>

    {{-- Status banner --}}
    <div class="mt-7 flex items-start gap-4 rounded-2xl border {{ $s['wrap'] }} p-5">
        <span
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $s['tile'] }} text-white shadow-sm">
            <i data-lucide="{{ $s['icon'] }}" class="h-5 w-5"></i>
        </span>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <p class="font-display text-base font-semibold {{ $s['text'] }}">{{ $s['label'] }}</p>
                <span
                    class="rounded-full bg-white/70 px-2 py-0.5 font-mono text-[10px] uppercase tracking-wide {{ $s['sub'] }}">status</span>
            </div>
            <p class="mt-0.5 text-sm {{ $s['sub'] }}">{{ $s['msg'] }}</p>
        </div>
    </div>

    @if (!$showForm)
        <div class="mt-6 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl {{ $status === 'verified' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                    <i data-lucide="{{ $status === 'verified' ? 'check-circle-2' : 'loader' }}" class="h-5 w-5"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $status === 'verified' ? 'Identity confirmed' : 'Documents submitted' }}</p>
                    <p class="text-sm text-slate-500">ID on file: <span
                            class="font-mono">{{ $verification->id_number ? '•••• ' . substr($verification->id_number, -4) : 'n/a' }}</span>
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Important warning --}}
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4">
            <i data-lucide="triangle-alert" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600"></i>
            <div>
                <p class="text-sm font-semibold text-rose-900">Fill in accurate, up-to-date personal data</p>
                <p class="mt-0.5 text-sm text-rose-700">All information must exactly match your national ID. Submitting
                    false or mismatched data may result in <strong>account suspension or permanent closure</strong>. Our
                    team manually reviews every submission.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data"
            class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-3">
            @csrf

            <div class="space-y-6 lg:col-span-2">

                {{-- Profile data card --}}
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-display text-base font-semibold text-slate-900">Personal data</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Must match your national ID exactly.</p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                            <i data-lucide="user" class="h-3 w-3"></i> Saved to your profile
                        </span>
                    </div>

                    <div class="mt-5 space-y-4">
                        {{-- Full name (readonly) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Full name <span class="text-rose-500">*</span>
                                <span class="ml-1 text-xs font-normal text-slate-400">(as on your ID)</span>
                            </label>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                                <span class="flex-1 text-sm text-slate-700">{{ $user->name }}</span>
                                <a href="{{ route('profile.edit') }}"
                                    class="shrink-0 text-xs text-indigo-600 hover:underline">Edit in profile</a>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">To update your name, go to Profile settings and re-submit
                                verification.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- DOB --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                    Date of birth <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="dob" max="{{ now()->subYears(17)->format('Y-m-d') }}"
                                    value="{{ $pval('dob') ? \Carbon\Carbon::parse($pval('dob'))->format('Y-m-d') : '' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                @error('dob')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                    Gender <span class="text-rose-500">*</span>
                                </label>
                                <x-select name="gender" :selected="$pval('gender')" placeholder="Select gender" :options="[
                                    ['value' => 'Male', 'label' => 'Male'],
                                    ['value' => 'Female', 'label' => 'Female'],
                                    ['value' => 'Other', 'label' => 'Other'],
                                ]" />
                                @error('gender')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone number</label>
                                <input type="tel" name="phone_number" value="{{ $pval('phone_number') }}"
                                    placeholder="+62 812 3456 7890"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                @error('phone_number')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                    City / domicile <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="city" value="{{ $pval('city') }}" placeholder="e.g. Jakarta"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                @error('city')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Profession --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Profession</label>
                                <input type="text" name="profession" value="{{ $pval('profession') }}"
                                    placeholder="e.g. Software Engineer"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                @error('profession')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Marital status --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Marital status</label>
                                <x-select name="marital_status" :selected="$pval('marital_status')" placeholder="Select status"
                                    :options="[
                                        ['value' => 'Single', 'label' => 'Single'],
                                        ['value' => 'Married', 'label' => 'Married'],
                                        ['value' => 'Divorced', 'label' => 'Divorced'],
                                        ['value' => 'Widowed', 'label' => 'Widowed'],
                                    ]" />
                                @error('marital_status')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Address <span class="text-rose-500">*</span>
                                <span class="ml-1 text-xs font-normal text-slate-400">(as on your national ID)</span>
                            </label>
                            <input type="text" name="address" value="{{ $pval('address') }}"
                                placeholder="Jl. Sudirman No. 1, Jakarta Pusat"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            @error('address')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ID number --}}
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <h2 class="font-display text-base font-semibold text-slate-900">National ID number <span
                            class="text-rose-500">*</span></h2>
                    <p class="mt-0.5 text-xs text-slate-500">Your national ID number, passport number, or equivalent
                        government-issued ID.</p>
                    <input type="text" name="id_number" value="{{ old('id_number', $verification->id_number) }}"
                        placeholder="e.g. A12345678 or 1234 5678 9012 3456"
                        class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    @error('id_number')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Document uploads --}}
                <div data-tour="verification-form" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- ID Card --}}
                    <div x-data="{ name: '', drag: false }" class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <i data-lucide="id-card" class="h-4.5 w-4.5"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">ID card <span
                                        class="text-rose-500">*</span></p>
                                <p class="text-xs text-slate-400">Passport, national ID, or driving licence</p>
                            </div>
                        </div>
                        <label @dragover.prevent="drag = true" @dragleave.prevent="drag = false" @drop="drag = false"
                            :class="drag ? 'border-indigo-400 bg-indigo-50/50' :
                                'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'"
                            class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-8 text-center transition">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <i data-lucide="upload-cloud" class="h-5 w-5"></i>
                            </span>
                            <p class="mt-3 text-sm font-medium text-slate-700" x-show="!name">Drop file or <span
                                    class="text-indigo-600">browse</span></p>
                            <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-emerald-600" x-show="name"
                                x-cloak>
                                <i data-lucide="check-circle-2" class="h-4 w-4"></i><span x-text="name"></span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400" x-show="!name">PNG or JPG, max 4 MB</p>
                            <input type="file" name="id_card" accept="image/*" class="hidden"
                                @change="name = $event.target.files[0]?.name ?? ''">
                        </label>
                        @error('id_card')
                            <p class="mt-2 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Selfie with ID --}}
                    <div x-data="{ name: '', drag: false }" class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-card">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                <i data-lucide="scan-face" class="h-4.5 w-4.5"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Selfie with national ID <span
                                        class="text-rose-500">*</span></p>
                                <p class="text-xs text-slate-400">Hold your ID card next to your face</p>
                            </div>
                        </div>
                        <label @dragover.prevent="drag = true" @dragleave.prevent="drag = false" @drop="drag = false"
                            :class="drag ? 'border-violet-400 bg-violet-50/50' :
                                'border-slate-200 hover:border-violet-300 hover:bg-slate-50'"
                            class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center transition">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <i data-lucide="camera" class="h-5 w-5"></i>
                            </span>
                            <p class="mt-3 text-sm font-medium text-slate-700" x-show="!name">Drop file or <span
                                    class="text-violet-600">browse</span></p>
                            <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-emerald-600" x-show="name"
                                x-cloak>
                                <i data-lucide="check-circle-2" class="h-4 w-4"></i><span x-text="name"></span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400" x-show="!name">Both your face and ID must be clearly
                                visible</p>
                            <input type="file" name="selfie" accept="image/*" class="hidden"
                                @change="name = $event.target.files[0]?.name ?? ''">
                        </label>
                        {{-- Guide strip --}}
                        <div class="mt-3 flex items-start gap-2 rounded-lg bg-violet-50 px-3 py-2.5">
                            <i data-lucide="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-violet-500"></i>
                            <p class="text-xs leading-relaxed text-violet-700">Hold your <strong>government-issued
                                    ID</strong> flat beside your face. No hats or sunglasses. Ensure both the photo on the
                                ID and your face are clearly readable.</p>
                        </div>
                        @error('selfie')
                            <p class="mt-2 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-2 text-xs text-slate-400">
                        <i data-lucide="lock" class="h-3.5 w-3.5"></i>
                        Documents are encrypted and only used for identity verification.
                    </p>
                    <button type="submit" data-tour="verification-submit"
                        class="dc-spectrum inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="shield-check" class="h-4 w-4"></i> Submit for review
                    </button>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5 lg:col-span-1">
                <div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                    <h2 class="font-display text-base font-semibold text-slate-900">Tips for approval</h2>
                    <ul class="mt-4 space-y-3.5">
                        @foreach ([['icon' => 'sun', 'text' => 'Use good lighting, avoid glare or shadows'], ['icon' => 'maximize', 'text' => 'Fit the entire document in the frame'], ['icon' => 'eye', 'text' => 'Keep all text sharp and readable'], ['icon' => 'id-card', 'text' => 'Show the front side of your ID card clearly'], ['icon' => 'scan-face', 'text' => 'For the selfie: hold your ID beside your face, both must be visible in the same photo'], ['icon' => 'x-circle', 'text' => 'No hats, sunglasses, or heavy filters']] as $tip)
                            <li class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                    <i data-lucide="{{ $tip['icon'] }}" class="h-3.5 w-3.5"></i>
                                </span>
                                <span class="text-sm text-slate-600">{{ $tip['text'] }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                        <p class="flex items-center gap-2 text-sm font-semibold text-indigo-900">
                            <i data-lucide="sparkles" class="h-4 w-4"></i> Why verify?
                        </p>
                        <p class="mt-1.5 text-xs leading-relaxed text-indigo-700/90">Verified accounts get a trust badge on
                            every dataset, unlock payouts, and earn higher buyer confidence.</p>
                    </div>
                </div>

                {{-- Warning card --}}
                <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shield-x" class="h-4 w-4 shrink-0 text-rose-600"></i>
                        <p class="text-sm font-semibold text-rose-900">Account protection</p>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-rose-700">Providing false, outdated, or mismatched
                        information is a violation of our Terms of Service. Accounts found to have discrepancies between
                        submitted data and national ID records may be <strong>suspended or permanently closed</strong>
                        without prior notice.</p>
                </div>
            </div>
        </form>
    @endif
@endsection
