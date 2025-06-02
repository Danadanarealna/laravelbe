<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Investor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UmkmController extends Controller
{
    public function index(Request $request)
    {
        if (! ($request->user() instanceof Investor) ) {
             return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $umkms = User::where('is_investable', true)
                     ->whereNotNull('umkm_name')
                     ->where('umkm_name', '!=', '')
                     ->whereNotNull('contact')
                     ->where('contact', '!=', '')
                     ->select('id', 'name', 'email', 'umkm_name', 'contact', 'is_investable', 'umkm_description', 'umkm_profile_image_path')
                     ->orderBy('umkm_name')
                     ->get();

        return response()->json($umkms);
    }

    public function show(Request $request, $umkmId)
    {
         if (! ($request->user() instanceof Investor) ) {
             return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $umkm = User::with(['transactions' => function ($query) {
                        $query->where('status', 'Done')->orderBy('user_sequence_id', 'desc');
                    }])
                    ->select('id', 'name', 'email', 'umkm_name', 'contact', 'is_investable', 'umkm_description', 'umkm_profile_image_path')
                    ->find($umkmId);

        if (!$umkm) {
            return response()->json(['message' => 'UMKM not found'], 404);
        }

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
                'name' => $umkm->name,
                'email' => $umkm->email,
                'umkm_name' => $umkm->umkm_name,
                'contact' => $umkm->contact,
                'is_investable' => $umkm->is_investable,
                'umkm_description' => $umkm->umkm_description,
                'umkm_profile_image_url' => $umkm->umkm_profile_image_url,
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
