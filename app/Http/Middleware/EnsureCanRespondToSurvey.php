<?php

namespace App\Http\Middleware;

use App\Models\Collection;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanRespondToSurvey
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Collection $collection */
        $collection = $request->route('collection');

        /** @var User $user */
        $user = $request->user();

        abort_unless($collection->status === 'ongoing', 404);

        if ($collection->survey_ended_at !== null) {
            return $this->deny(__('This survey has been ended by its creator and is no longer accepting responses.'));
        }

        if ($collection->user_id === $user->id) {
            return $this->deny(__('You cannot fill your own survey.'));
        }

        $user->loadMissing('verification');

        if ($user->verification?->status !== 'verified') {
            return redirect()->route('verification.index')
                ->with('error', __('Identity verification is required before filling surveys.'));
        }

        if ($collection->entries()->where('user_id', $user->id)->exists()) {
            return $this->deny(__('You have already submitted a response to this survey.'));
        }

        if ($collection->respondent_target && $collection->entries()->count() >= $collection->respondent_target) {
            return $this->deny(__('This survey has reached its target and is no longer accepting responses.'));
        }

        return $next($request);
    }

    private function deny(string $message): Response
    {
        return redirect()->route('surveys.index')->with('error', $message);
    }
}
