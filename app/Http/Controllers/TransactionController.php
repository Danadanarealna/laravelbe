<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    private $allowedTransactionTypes = ['Income', 'Expense', 'DebtIncurred', 'Investment'];
    private $allowedPaymentMethods = ['Cash', 'Credit', 'Bank Transfer', 'E-Wallet', 'Other'];
    private $allowedTransactionStatuses = ['Pending', 'Done', 'Cancelled'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $transactions = $user->transactions()
            ->orderBy('date', 'desc')
            ->orderBy('user_sequence_id', 'desc') 
            ->get();

        return response()->json($transactions->map(function (Transaction $transaction) {
            return [
                'id' => $transaction->id,
                'user_sequence_id' => $transaction->user_sequence_id,
                'amount' => (float)$transaction->amount,
                'type' => $transaction->type,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'date' => Carbon::parse($transaction->date)->format('Y-m-d'),
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at->toIso8601String(),
                'updated_at' => $transaction->updated_at->toIso8601String(),
            ];
        }));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($request->has('date')) {
            $dateInput = $request->input('date');
            try {
                $parsedDate = Carbon::createFromFormat('d M Y', $dateInput)->format('Y-m-d');
                if (!$parsedDate) {
                     $parsedDate = Carbon::createFromFormat('yyyy-MM-dd', $dateInput)->format('Y-m-d');
                }
                $request->merge(['date' => $parsedDate]);
            } catch (\Exception $e) {
                 try {
                    Carbon::parse($dateInput);
                 } catch (\Exception $ex) {
                    return response()->json(['errors' => ['date' => ['The date format is invalid. Please use yyyy-MM-DD or let the app format it.']]], 422);
                 }
            }
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'type' => ['required', 'string', Rule::in($this->allowedTransactionTypes)],
            'payment_method' => ['required', 'string', Rule::in($this->allowedPaymentMethods)],
            'status' => ['required', 'string', Rule::in($this->allowedTransactionStatuses)],
            'date' => 'required|date_format:Y-m-d',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $data['date'] = Carbon::parse($data['date'])->startOfDay()->toDateTimeString();
        
        $lastSequence = Transaction::where('user_id', $user->id)->max('user_sequence_id');
        $data['user_sequence_id'] = ($lastSequence ?? 0) + 1;

        $transaction = $user->transactions()->create($data);

        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'payment_method' => $transaction->payment_method,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('Y-m-d'),
            'notes' => $transaction->notes,
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'payment_method' => $transaction->payment_method,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('Y-m-d'),
            'notes' => $transaction->notes,
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->has('date')) {
            $dateInput = $request->input('date');
            try {
                $parsedDate = Carbon::createFromFormat('d M Y', $dateInput)->format('Y-m-d');
                 if (!$parsedDate) {
                     $parsedDate = Carbon::createFromFormat('yyyy-MM-dd', $dateInput)->format('Y-m-d');
                }
                $request->merge(['date' => $parsedDate]);
            } catch (\Exception $e) {
                 try {
                    Carbon::parse($dateInput);
                 } catch (\Exception $ex) {
                    return response()->json(['errors' => ['date' => ['The date format is invalid.']]], 422);
                 }
            }
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric',
            'type' => ['sometimes','required','string', Rule::in($this->allowedTransactionTypes)],
            'payment_method' => ['sometimes','required','string', Rule::in($this->allowedPaymentMethods)],
            'status' => ['sometimes','required','string', Rule::in($this->allowedTransactionStatuses)],
            'date' => 'sometimes|required|date_format:Y-m-d',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        if (isset($data['date'])) {
            $data['date'] = Carbon::parse($data['date'])->startOfDay()->toDateTimeString();
        }

        $transaction->update($data);

        return response()->json([
            'id' => $transaction->id,
            'user_sequence_id' => $transaction->user_sequence_id,
            'amount' => (float)$transaction->amount,
            'type' => $transaction->type,
            'payment_method' => $transaction->payment_method,
            'status' => $transaction->status,
            'date' => Carbon::parse($transaction->date)->format('Y-m-d'),
            'notes' => $transaction->notes,
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }
}
