<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectVerificationRequest;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'users' => User::count(),
            'pending' => Verification::where('status', 'pending')->count(),
            'verified' => Verification::where('status', 'verified')->count(),
            'collections' => Collection::count(),
        ];

        $breakdown = Verification::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $filter = $request->query('status', 'all');
        $search = $request->query('q');

        $query = User::query()->with(['verification', 'profile'])->latest();

        if (in_array($filter, ['pending', 'verified', 'rejected', 'unverified'], true)) {
            $query->whereHas('verification', fn($q) => $q->where('status', $filter));
        }

        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $users = $query->paginate(8)->withQueryString();

        $chart = $this->monthlySignups();
        return view('admin.dashboard', compact('stats', 'breakdown', 'chart', 'users', 'filter', 'search'));
    }

    public function approve(string $locale, User $user)
    {
        $user->verification()->updateOrCreate([], ['status' => 'verified', 'note' => null]);

        Activity::log(
            $user->id,
            'system',
            __('Account verified'),
            __('An admin approved your verification. You can now sell datasets.'),
        );

        return back()->with('success', __(':name has been verified.', ['name' => $user->name]));
    }

    public function reject(string $locale, RejectVerificationRequest $request, User $user)
    {
        $note = $request->note();

        $user->verification()->updateOrCreate([], [
            'status' => 'rejected',
            'note'   => $note,
        ]);

        Activity::log($user->id, 'system', __('Verification rejected'), $note);

        return back()->with('success', __("The verification for :name was rejected.", ['name' => $user->name]));
    }

    private function monthlySignups(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $counts = User::where('created_at', '>=', $start)->get()
            ->groupBy(fn(User $u) => $u->created_at->format('Y-m'))
            ->map->count();

        $labels = [];
        $values = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M');
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values, 'max' => max(1, max($values))];
    }

    public function document(string $locale, User $user, string $type)
    {
        abort_unless(in_array($type, ['id_card', 'selfie'], true), 404);

        $raw = $type === 'id_card'
            ? $user->verification?->id_card_url
            : $user->verification?->selfie_url;

        abort_if(blank($raw), 404);

        // Older rows stored a "/storage/…" URL prefix even for files on the private
        // disk; normalise both shapes down to a disk-relative path.
        $path = preg_replace('#^storage/#', '', ltrim($raw, '/'));

        abort_if(str_contains($path, '..'), 404);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return response()->file(Storage::disk($disk)->path($path));
            }
        }

        abort(404);
    }
}
