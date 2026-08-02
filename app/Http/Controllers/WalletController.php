<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\TopupRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $earned = (int) $user->transactions()
            ->where('direction', 'credit')->where('status', 'success')->where('type', '!=', 'topup')->sum('amount');

        $spent = (int) $user->transactions()
            ->where('direction', 'debit')->where('status', 'success')->where('type', '!=', 'withdraw')->sum('amount');

        return view('wallet.index', [
            'balance'      => $user->balance(),
            'earned'       => $earned,
            'spent'        => $spent,
            'transactions' => $user->transactions()->latest()->take(6)->get(),
        ]);
    }

    public function topup(TopupRequest $request)
    {
        $data   = $request->validated();
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
            return redirect()->route('wallet.index')->with('error', __('No pending payment found.'));
        }

        $amount = (int) $instruction['amount'];

        $wallet->credit(Auth::user(), $amount, 'topup', [
            'payment_method_id' => $instruction['payment_method_id'] ?? null,
            'description'       => __('Top-up via :method', ['method' => $instruction['method']]),
            'activity'          => __('Wallet topped up with :amount', ['amount' => Money::format($amount)]),
        ]);

        return redirect()->route('wallet.index')
            ->with('success', __(':amount added to your wallet.', ['amount' => Money::format($amount)]));
    }

    public function cancel()
    {
        session()->forget('topup_instruction');

        return redirect()->route('wallet.index');
    }

    public function withdraw(WithdrawRequest $request, WalletService $wallet)
    {
        /** @var \App\Models\User $user */
        $user   = Auth::user();
        $data   = $request->validated();
        $amount = (int) $data['amount'];

        try {
            $wallet->debit($user, $amount, 'withdraw', [
                'description' => __('Withdrawal to :bank · :account', ['bank' => $data['bank_name'], 'account' => $data['account_number']]),
                'status'      => 'processing',
                'activity'    => __('Withdrawal of :amount requested to :bank', ['amount' => Money::format($amount), 'bank' => $data['bank_name']]),
            ]);
        } catch (InsufficientBalanceException) {
            return back()->with('error', __('Insufficient balance.'))->withInput();
        }

        return back()->with('success', __('Withdrawal of :amount is being processed. Funds will arrive in 1–3 business days.', [
            'amount' => Money::format($amount),
        ]));
    }
}
