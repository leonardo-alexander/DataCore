<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $earned = (int) $user->transactions()->where('direction', 'credit')->where('status', 'success')->where('type', '!=', 'topup')->sum('amount');
        $spent = (int) $user->transactions()->where('direction', 'debit')->where('status', 'success')->where('type', '!=', 'withdraw')->sum('amount');

        $transactions = $user->transactions()->latest()->take(6)->get();

        return view('wallet.index', [
            'balance'      => $user->balance(),
            'earned'       => $earned,
            'spent'        => $spent,
            'transactions' => $transactions,
        ]);
    }

    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount'            => ['required', 'integer', 'min:'.config('datacore.min_topup'), 'max:'.config('datacore.max_topup')],
            'method'            => ['required', 'string'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
        ]);

        $amount = (int) $data['amount'];
        $method = $data['method'];
        $ref    = 'DC-' . strtoupper(Str::random(8));

        $instruction = [
            'amount'            => $amount,
            'method'            => $method,
            'ref'               => $ref,
            'payment_method_id' => $data['payment_method_id'] ?? null,
        ];

        if ($method === 'Virtual Account') {
            $instruction['va_bank']   = collect(['BCA', 'BNI', 'Mandiri'])->random();
            $instruction['va_number'] = '8800' . str_pad((string) rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } elseif ($method === 'QRIS') {
            $instruction['qr_payload'] = json_encode([
                'app'         => 'DataCore',
                'merchant_id' => 'DC-MERCHANT-001',
                'amount'      => $amount,
                'ref'         => $ref,
                'currency'    => 'IDR',
            ]);
        } elseif ($method === 'E-wallet') {
            $instruction['ewallet_name']   = collect(['GoPay', 'OVO', 'Dana'])->random();
            $instruction['ewallet_number'] = '08' . str_pad((string) rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        }

        session()->put('topup_instruction', $instruction);

        return redirect()->route('wallet.index');
    }

    public function confirm(WalletService $wallet)
    {
        $instruction = session()->pull('topup_instruction');

        if (! $instruction) {
            return redirect()->route('wallet.index')->with('error', 'No pending payment found.');
        }

        $wallet->credit(Auth::user(), (int) $instruction['amount'], 'topup', [
            'payment_method_id' => $instruction['payment_method_id'] ?? null,
            'description'       => 'Top-up via ' . $instruction['method'],
            'activity'          => 'Wallet topped up with ' . \App\Support\Money::format($instruction['amount']),
        ]);

        return redirect()->route('wallet.index')
            ->with('success', \App\Support\Money::format($instruction['amount']) . ' added to your wallet.');
    }

    public function cancel()
    {
        session()->forget('topup_instruction');

        return redirect()->route('wallet.index');
    }

    public function withdraw(Request $request, WalletService $wallet)
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $balance = $user->balance();
        $min     = config('datacore.min_withdrawal');

        $data = $request->validate([
            'amount'         => ['required', 'integer', 'min:' . $min, 'max:' . max($balance, 1)],
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name'   => ['required', 'string', 'max:100'],
        ], [
            'amount.max' => 'The withdrawal amount exceeds your available balance.',
            'amount.min' => 'Minimum withdrawal is ' . \App\Support\Money::format($min) . '.',
        ]);

        $amount = (int) $data['amount'];

        try {
            $wallet->debit($user, $amount, 'withdraw', [
                'description' => 'Withdrawal to ' . $data['bank_name'] . ' · ' . $data['account_number'],
                'status'      => 'processing',
                'activity'    => 'Withdrawal of ' . \App\Support\Money::format($amount) . ' requested to ' . $data['bank_name'],
            ]);
        } catch (InsufficientBalanceException) {
            return back()->with('error', 'Insufficient balance.')->withInput();
        }

        return back()->with('success',
            'Withdrawal of ' . \App\Support\Money::format($amount) . ' is being processed. Funds will arrive in 1–3 business days.'
        );
    }
}
