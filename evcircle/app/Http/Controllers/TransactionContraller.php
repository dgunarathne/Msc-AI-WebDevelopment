<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionContraller extends Controller
{
public function index(Request $request)
{
    $user = $request->user();

    // Calculate total balance
    $balance = Transaction::where('user_id', $user->id)->sum('amount');

    // Fetch latest 20 transactions
    $transactions = Transaction::where('user_id', $user->id)
        ->latest()
        ->take(20)
        ->get()
        ->map(function ($txn) {
            return [
                'id' => $txn->id,
                'type' => ucfirst($txn->type),
                'amount' => $txn->amount,
                'date' => $txn->created_at->toDateString(),
                'icon' => $txn->type === 'credit' ? '💰' : '💸',
                'description' => $txn->reason ?? 'Transaction',
            ];
        });

    // Get current month and year
    $currentMonth = now()->month;
    $currentYear = now()->year;

    // Calculate this month's total amount and count
    $monthlyTransactionsQuery = Transaction::where('user_id', $user->id)
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear);

    $monthlyTotal = $monthlyTransactionsQuery->sum('amount');
    $monthlyCount = $monthlyTransactionsQuery->count();

    return response()->json([
        'balance' => $balance,
        'transactions' => $transactions,
        'this_month_total' => $monthlyTotal,
        'this_month_count' => $monthlyCount,
    ]);
}

}
