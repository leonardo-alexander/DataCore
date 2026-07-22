<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $collectionIds = $user->collections()->pluck('id');

        $entriesCount   = Entry::whereIn('collection_id', $collectionIds)->count();
        $publishedCount = $user->collections()->where('status', 'published')->count();
        $avgQuality     = round((float) $user->collections()->whereNotNull('quality_score')->avg('quality_score'), 2);

        $stats = [
            'balance'   => $user->balance(),
            'entries'   => $entriesCount,
            'published' => $publishedCount,
            'quality'   => $avgQuality,
        ];

        $activities = $user->activities()->latest()->take(6)->get();

        $profile   = $user->profile;
        $checklist = [
            ['key' => 'profile',    'done' => $profile && ($profile->phone_number || $profile->dob || $profile->address), 'label' => __('Complete your profile'), 'desc' => __('Add your phone number, date of birth, or address.'), 'route' => 'profile.edit', 'icon' => 'user'],
            ['key' => 'topup',      'done' => $user->transactions()->where('type', 'topup')->where('status', 'success')->exists(), 'label' => __('Top up your wallet'), 'desc' => __('Fund your wallet to pay for Full Clean and survey rewards.'), 'route' => 'wallet.index', 'icon' => 'wallet'],
            ['key' => 'collection', 'done' => $user->collections()->exists(), 'label' => __('Create a collection'), 'desc' => __('Build a survey or import a CSV dataset.'), 'route' => 'collections.create', 'icon' => 'layers'],
            ['key' => 'entry',      'done' => $entriesCount > 0, 'label' => __('Collect your first entry'), 'desc' => __('Share your survey and wait for responses to come in.'), 'route' => 'collections.index', 'icon' => 'database'],
            ['key' => 'clean1',     'done' => Entry::whereIn('collection_id', $collectionIds)->whereNotNull('clean1_data')->exists(), 'label' => __('Run Half Clean'), 'desc' => __('Strip PII and generate a quality score. It\'s free.'), 'route' => 'collections.index', 'icon' => 'shield-check'],
            ['key' => 'publish',    'done' => $publishedCount > 0, 'label' => __('Publish a dataset'), 'desc' => __('List your cleaned data on the marketplace and start earning.'), 'route' => 'collections.index', 'icon' => 'rocket'],
        ];

        $checklistDone = collect($checklist)->every(fn ($s) => $s['done']);

        return view('dashboard', compact('stats', 'activities', 'checklist', 'checklistDone'));
    }
}
