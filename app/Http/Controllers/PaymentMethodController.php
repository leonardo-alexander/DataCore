<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:bank,ewallet,card'],
            'name' => ['required', 'string', 'max:255'],
            'account' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->paymentMethods()->create([
            'type' => $data['type'],
            'name' => $data['name'],
            'details' => ['account' => $data['account']],
            'is_active' => true,
        ]);

        return back()->with('success', 'Payment method added.');
    }

    public function destroy(string $locale, PaymentMethod $paymentMethod)
    {
        abort_unless($paymentMethod->user_id === Auth::id(), 403);
        $paymentMethod->delete();

        return back()->with('success', 'Payment method removed.');
    }
}
