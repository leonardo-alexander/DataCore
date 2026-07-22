<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('type', 'all');

        $query = $user->transactions()->with('collection')->latest();

        if (in_array($filter, ['topup', 'payment', 'reward', 'refund', 'withdraw'], true)) {
            $query->where('type', $filter);
        }

        $transactions = $query->paginate(12)->withQueryString();

        $totalIn = (int) $user->transactions()->where('direction', 'credit')->where('status', 'success')->sum('amount');
        $totalOut = (int) $user->transactions()->where('direction', 'debit')->where('status', 'success')->sum('amount');

        return view('transactions.index', compact('transactions', 'filter', 'totalIn', 'totalOut'));
    }
}
