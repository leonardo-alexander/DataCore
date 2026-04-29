<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_method_id' => 'required|exists:payment_methods, id'
        ]);
        DB::transaction(function () use ($request){

            $wallet = $request->user()->wallet()->lockForUpdate()->first();

            $wallet->balance += $request->amount;
            $wallet->save();

            Transaction::create([
                'user_id' => $request->user()->id,
                'type' => 'topup',
                'direction' => 'credit',
                'amount'=> $request->amount,
                'status' => 'success',
                'payment_method_id' => $request->payment_method_id
            ]);
        });
        return response()->json([
            'message' => 'Top-up success'
        ]);
    }

    // public function purchase(Request $request)
    // {
    //     $request->validate([
    //         'collection_id' => 'required|exists:collections,id'
    //     ]);

    //     DB::transaction(function () use ($request){
    //         $buyer = $request->user();
    //         $collection = Collection::findOrFail($request->collection_id);

    //         $price = $collection->price;
    //         $sellerId = $collection->user_id;

    //         $buyerWallet = $buyer->wallet()->lockForUpdate()->first();

    //         if ($buyerWallet->balance < $price) {
    //             abort(422, 'Insufficient balance');
    //         }

    //         $buyerWallet->balance -= $price;
    //         $buyerWallet->save();

    //         $sellerWallet = Wallet::where('user_id', $sellerId)
    //             ->lockForUpdate()
    //             ->first();

    //         $sellerAmount = $price * 0.9;

    //         $sellerWallet->balance += $sellerAmount;
    //         $sellerWallet->save();

    //         Transaction::create([
    //             'user_id' => $buyer->id,
    //             'type' => 'payment',
    //             'direction' => 'debit',
    //             'amount' => $price,
    //             'status' => 'success'
    //         ]);
    //     });
    //     return response()->json([
    //         'message' =>'Purchase success'
    //     ]);
    // }

    public function reward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {

            $wallet = Wallet::where('user_id', $request->user_id)
                ->lockForUpdate()
                ->first();

            $wallet->balance += $request->amount;
            $wallet->save();

            Transaction::create([
                'user_id' => $request->user_id,
                'type' => 'reward',
                'direction' => 'credit',
                'amount' => $request->amount,
                'status' => 'success'
            ]);
        });

        return response()->json([
            'message' => 'Reward success'
        ]);
    }

    public function surveyDistribution(Request $request)
    {
        $request->validate([
            'responders' => 'required|integer|min:1',
            'reward_each' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {

            $total = $request->responders * $request->reward_each;

            $wallet = $request->user()->wallet()->lockForUpdate()->first();

            if ($wallet->balance < $total) {
                abort(422, 'Insufficient balance');
            }

            $wallet->balance -= $total;
            $wallet->save();

            Transaction::create([
                'user_id' => $request->user()->id,
                'type' => 'payment',
                'direction' => 'debit',
                'amount' => $total,
                'status' => 'success'
            ]);
        });

        return response()->json([
            'message' => 'Survey distribution paid'
        ]);
    }

    public function dataCleaning(Request $request)
    {
        $request->validate([
            'strict' => 'required|boolean'
        ]);

        if (!$request->strict) {
            return response()->json([
                'message' => 'Standard cleaning success (free)'
            ]);
        }

        DB::transaction(function () use ($request) {

            $wallet = $request->user()->wallet()->lockForUpdate()->first();

            if ($wallet->balance < 50) {
                abort(422, 'Insufficient balance');
            }

            $wallet->balance -= 50;
            $wallet->save();

            Transaction::create([
                'user_id' => $request->user()->id,
                'type' => 'payment',
                'direction' => 'debit',
                'amount' => 50,
                'status' => 'success'
            ]);
        });

        return response()->json([
            'message' => 'Strict cleaning success'
        ]);
    }
}