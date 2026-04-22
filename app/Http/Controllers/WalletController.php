<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{  
    public function balance(Request $request){
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => $wallet->balance
        ]);
        
    }

    public function topUp(Request $request){
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);
        DB::transaction(function () use ($request){
            $wallet = $request->user()->wallet()->lockForUpdate()->first();
            $wallet->balance += $request->amount;

            $wallet->save();
        });
    }

    public function pay(Request $request){
        $request->validate([
            'amount'=> ['required|integer|min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $balance = $request->user()->wallet->balance;

                    if ($value > $balance) {
                        $fail('Insufficient balance.')
                    }
                }
            ]
        ]);

        DB::transaction(function () use ($request){
            $wallet = $request->user()->wallet()->lockForUpdate()->first();

            $wallet->balance -= $request->amount;
            $wallet->save();
        });
    }
}
