<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class WalletController extends Controller
{  
    public function balance(Request $request){
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => $wallet->balance
        ]);
        
    }
}
