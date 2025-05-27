<?php

namespace App\Http\Controllers;

use App\Models\User; // UMKM User Model
use App\Models\Investor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UmkmController extends Controller
{
    /**
     * Display a listing of UMKM profiles that are "investable" and "complete".
     * "Complete" means umkm_name and contact are filled.
     */
    public function index(Request $request)
    {
        // Ensure only authenticated investors can access this
        // This middleware should be applied in routes/api.php: ['auth:sanctum', 'ability:role:investor']
        if (! ($request->user() instanceof Investor) ) {
             return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $umkms = User::where('is_investable', true) // <<< CRUCIAL FILTER ADDED
                     ->whereNotNull('umkm_name')
                     ->where('umkm_name', '!=', '')
                     ->whereNotNull('contact') // Using standardized 'contact' field
                     ->where('contact', '!=', '')
                     ->select('id', 'name', 'email', 'umkm_name', 'contact', 'is_investable') // Select relevant fields
                     ->orderBy('umkm_name')
                     ->get();

        return response()->json($umkms);
    }

    /**
     * Display the specified UMKM profile along with their financial summary (transactions).
     */
    public function show(Request $request, $umkmId) // umkmId is the ID of the User (UMKM)
    {
         if (! ($request->user() instanceof Investor) ) {
             return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $umkm = User::with(['transactions' => function ($query) {
                        // For financial summary, usually only 'Done' transactions are considered.
                        $query->where('status', 'Done')->orderBy('user_sequence_id', 'desc'); // Or 'date'
                    }])
                    ->select('id', 'name', 'email', 'umkm_name', 'contact', 'is_investable')
                    ->find($umkmId);

        if (!$umkm) {
            return response()->json(['message' => 'UMKM not found'], 404);
        }

        // Ensure the profile is "complete" and investable before showing detailed financials
        if (empty($umkm->umkm_name) || empty($umkm->contact) || !$umkm->is_investable) {
            return response()->json(['message' => 'This UMKM profile is not complete or not open for investment viewing.'], 403);
        }

        $formattedTransactions = $umkm->transactions->map(function (Transaction $transaction) {
            return [
                'id' => $transaction->id,
                'user_sequence_id' => $transaction->user_sequence_id,
                'amount' => (float)$transaction->amount,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'date' => Carbon::parse($transaction->date)->format('d M Y'),
            ];
        });

        $totalIncome = $umkm->transactions->where('amount', '>', 0)->sum('amount');
        $totalExpense = $umkm->transactions->where('amount', '<', 0)->sum('amount');

        return response()->json([
            'umkm' => [
                'id' => $umkm->id,
                'name' => $umkm->name, // Owner's name
                'email' => $umkm->email,
                'umkm_name' => $umkm->umkm_name,
                'contact' => $umkm->contact, // Using standardized 'contact' field
                'is_investable' => $umkm->is_investable,
            ],
            'financial_summary' => [
                'total_income' => (float) $totalIncome,
                'total_expense' => (float) abs($totalExpense),
                'net_profit' => (float) ($totalIncome + $totalExpense)
            ],
            'transactions' => $formattedTransactions,
        ]);
    }
}
