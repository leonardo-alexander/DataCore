<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLanguageRequest;

class LanguageController extends Controller
{
    public function update(UpdateLanguageRequest $request)
    {
        $locale = $request->validated('locale');

        session(['locale' => $locale]);

        $segments = collect(explode('/', ltrim((string) parse_url(url()->previous(), PHP_URL_PATH), '/')))
            ->whenEmpty(fn ($segments) => $segments->push($locale))
            ->replace([0 => $locale]);

        $redirect = '/' . $segments->implode('/');

        if ($request->expectsJson()) {
            return response()->json(['redirect' => $redirect]);
        }

        return redirect($redirect);
    }
}
