<?php

namespace App\Http\Controllers;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletContraller extends Controller
{

public function Rechrage_wallet(Request $request){
       $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user(); // Get authenticated user

        // Find wallet or create if not exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['amount' => 0]
        );

        // Update wallet balance
        $wallet->amount += $request->amount;
        $wallet->save();

        return response()->json([
            'message' => 'Wallet recharged successfully!',
            'wallet_balance' => $wallet->amount,
        ], 200);
}

public function get_tra_history(Request $request){

}

}
