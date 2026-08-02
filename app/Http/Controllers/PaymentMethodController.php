<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function store(StorePaymentMethodRequest $request)
    {
        $data = $request->validated();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->paymentMethods()->create([
            'type'      => $data['type'],
            'name'      => $data['name'],
            'details'   => ['account' => $data['account']],
            'is_active' => true,
        ]);

        return back()->with('success', __('Payment method added.'));
    }

    public function destroy(string $locale, PaymentMethod $paymentMethod)
    {
        abort_unless($paymentMethod->user_id === Auth::id(), 403);

        $paymentMethod->delete();

        return back()->with('success', __('Payment method removed.'));
    }
}
