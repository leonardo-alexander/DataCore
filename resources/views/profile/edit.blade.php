@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    @php
        $profile = $user->profile;
        $verified = $user->isVerified();
        $genders = \App\Http\Requests\UpdateProfileRequest::GENDERS;
        $maritalStatuses = \App\Http\Requests\UpdateProfileRequest::MARITAL_STATUSES;
    @endphp

    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-slate-900">Profile</h1>
        <p class="mt-1 text-sm text-slate-500">This information appears on your collections, reviews, and sales.</p>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
        class="mt-7 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')

        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200/70 bg-white p-6 text-center shadow-card">
                <!-- <div class="relative mx-auto h-28 w-28">
                            @if ($profile?->picture_url)
    <img src="{{ $profile->picture_url }}" alt="Avatar" class="h-28 w-28 rounded-full object-cover shadow-glow">
@else
    <div class="dc-spectrum flex h-28 w-28 items-center justify-center rounded-full text-3xl font-semibold text-white shadow-glow">{{ strtoupper(mb_substr($user->name, 0, 2)) }}</div>
    @endif
                            <label class="absolute bottom-1 right-1 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white transition hover:bg-slate-700">
                                <i data-lucide="camera" class="h-4 w-4"></i>
                                <input type="file" name="image" accept="image/*" class="hidden">
                            </label>
                        </div> -->
                <div class="relative mx-auto h-28 w-28">
                    @if ($profile?->picture_url)
                        <img id="avatar-preview" src="{{ $profile->picture_url }}" alt="Avatar"
                            class="h-28 w-28 rounded-full object-cover shadow-glow">
                        <div id="avatar-placeholder"
                            class="dc-spectrum hidden h-28 w-28 items-center justify-center rounded-full text-3xl font-semibold text-white shadow-glow">
                            {{ strtoupper(mb_substr($user->name, 0, 2)) }}</div>
                    @else
                        <img id="avatar-preview" src="" alt="Avatar"
                            class="hidden h-28 w-28 rounded-full object-cover shadow-glow">
                        <div id="avatar-placeholder"
                            class="dc-spectrum flex h-28 w-28 items-center justify-center rounded-full text-3xl font-semibold text-white shadow-glow">
                            {{ strtoupper(mb_substr($user->name, 0, 2)) }}</div>
                    @endif
                    <label
                        class="absolute bottom-1 right-1 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white transition hover:bg-slate-700">
                        <i data-lucide="camera" class="h-4 w-4"></i>
                        <input id="avatar-input" type="file" name="image" accept="image/png, image/jpeg, image/jpg"
                            class="hidden">
                    </label>
                </div>
                <p class="mt-4 font-display text-lg font-semibold text-slate-900">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                @error('image')
                    <p class="mt-2 text-xs text-rose-500">{{ $message }}</p>
                @enderror
                @if ($verified)
                    <a href="{{ route('verification.index') }}"
                        class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        <i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Verified contributor
                    </a>
                @else
                    <a href="{{ route('verification.index') }}"
                        class="dc-spectrum mt-3 inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                        <i data-lucide="badge-check" class="h-4 w-4"></i> Get verified
                        <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                    </a>
                @endif
                <p class="mt-4 text-xs text-slate-400">JPG or PNG, up to 2MB.</p>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div data-tour="profile-fields" class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-card">
                <h2 class="font-display text-lg font-semibold text-slate-900">Personal details</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Full name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
                        <input type="tel" name="phone_number"
                            value="{{ old('phone_number', $profile?->phone_number) }}" placeholder="+62 812 0000 0000"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Gender</label>
                        <x-select
                            name="gender"
                            :selected="old('gender', $profile?->gender)"
                            placeholder="Select gender"
                            :options="collect($genders)->map(fn($g) => ['value' => $g, 'label' => $g])->all()"
                        />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Date of birth</label>
                        <input type="date" name="dob" max="{{ now()->subYears(17)->format('Y-m-d') }}"
                            value="{{ old('dob', optional($profile?->dob)->format('Y-m-d')) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">City / domicile</label>
                        <input type="text" name="city" value="{{ old('city', $profile?->city) }}" placeholder="Jakarta"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Profession</label>
                        <input type="text" name="profession" value="{{ old('profession', $profile?->profession) }}" placeholder="Data Analyst"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Marital status</label>
                        <x-select
                            name="marital_status"
                            :selected="old('marital_status', $profile?->marital_status)"
                            placeholder="Select marital status"
                            :options="collect($maritalStatuses)->map(fn($s) => ['value' => $s, 'label' => $s])->all()"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" rows="2" placeholder="Street, city, postal code"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">{{ old('address', $profile?->address) }}</textarea>
                    </div>
                </div>
                <p class="mt-4 flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-xs text-slate-500">
                    <i data-lucide="info" class="mt-0.5 h-3.5 w-3.5 shrink-0"></i>
                    <span>Surveys can ask to attach your age, gender, city, profession, or marital status to your entry. Only the fields a survey asks for are attached, and only if you have filled them in here.</span>
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('dashboard') }}"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="dc-spectrum inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                    <i data-lucide="check" class="h-4 w-4"></i> Save changes
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                alert('File harus berformat JPG atau PNG.');
                e.target.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB.');
                e.target.value = '';
                return;
            }

            const preview = document.getElementById('avatar-preview');
            const placeholder = document.getElementById('avatar-placeholder');
            const reader = new FileReader();

            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>
@endpush
