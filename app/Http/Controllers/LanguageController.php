<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'locale' => ['required', 'in:en,id'],
        ]);

        session(['locale' => $data['locale']]);

        $path     = parse_url(url()->previous(), PHP_URL_PATH) ?? '/';
        $segments = explode('/', ltrim($path, '/'));
        $segments[0] = $data['locale'];
        $redirect = '/' . implode('/', $segments);

        if ($request->expectsJson()) {
            return response()->json(['redirect' => $redirect]);
        }

        return redirect($redirect);
    }
}
