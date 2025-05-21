<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->transactions()
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => (float)$transaction->amount,
                    'type' => $transaction->type,
                    'status' => $transaction->status,
                    'date' => $transaction->date->format('d M Y'),
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at
                ];
            });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:Cash,Credit',
            'status' => 'required|in:Pending,Done,Cancelled',
            'date' => 'required|date_format:d M Y'
        ]);
    
        $data['date'] = Carbon::createFromFormat('d M Y', $data['date']);
    
        return $request->user()->transactions()->create($data);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }
    
        $data = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:Cash,Credit',
            'status' => 'required|in:Pending,Done,Cancelled',
            'date' => 'required|date_format:d M Y'
        ]);
    
        $data['date'] = Carbon::createFromFormat('d M Y', $data['date']);
        $transaction->update($data);
    
        return $transaction;
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}