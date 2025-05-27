<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $transactions = $user->transactions()
            ->orderBy('user_sequence_id', 'desc') // Default order by per-user sequence
            ->get();

        return response()->json($transactions->map(function (Transaction $transaction) {
            return [
                'id' => $transaction->id, // Global unique primary ID
                'user_sequence_id' => $transaction->user_sequence_id, // Per-user display ID
                'amount' => (float)$transaction->amount,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'date' => Carbon::parse($transaction->date)->format('d M Y'),
                'created_at' => $transaction->created_at->toIso8601String(),
                'updated_at' => $transaction->updated_at->toIso8601String(),
            ];
        }));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $data = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string|in:Cash,Credit',
            'status' => 'required|string|in:Pending,Done,Cancelled',
            'date' => 'required|date_format:d M Y',
        ]);

        $data['date'] = Carbon::createFromFormat('d M Y', $data['date'])->startOfDay()->toDateTimeString();
        $lastSequence = Transaction::where('user_id', $user->id)->max('user_sequence_id');
        $data['user_sequence_id'] = ($lastSequence ?? 0) + 1;

        $transaction = $user->transactions()->create($data);

        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('d M Y'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('d M Y'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $data = $request->validate([
            'amount' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|string|in:Cash,Credit',
            'status' => 'sometimes|required|string|in:Pending,Done,Cancelled',
            'date' => 'sometimes|required|date_format:d M Y',
        ]);
        if (isset($data['date'])) {
            $data['date'] = Carbon::createFromFormat('d M Y', $data['date'])->startOfDay()->toDateTimeString();
        }
        $transaction->update($data);
        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('d M Y'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }
}