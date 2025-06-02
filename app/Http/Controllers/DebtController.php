<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DebtController extends Controller
{
    private $allowedDebtStatuses = [
        Debt::STATUS_PENDING_VERIFICATION,
        Debt::STATUS_VERIFIED_INCOME_RECORDED,
        Debt::STATUS_REPAID_BY_UMKM,
        Debt::STATUS_CANCELLED,
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            Log::warning('Authenticated user is not an instance of App\Models\User in DebtController@index');
            return response()->json([], 200);
        }

        $debts = $user->debts()
                      ->orderBy('deadline', 'asc')
                      ->get();

        return response()->json($debts->map(function ($debt) {
            return [
                'id' => $debt->id,
                'amount' => (float)$debt->amount,
                'date' => $debt->date->format('Y-m-d'),
                'deadline' => $debt->deadline->format('Y-m-d'),
                'status' => $debt->status,
                'notes' => $debt->notes,
                'created_at' => $debt->created_at->toIso8601String(),
                'related_transaction_id' => $debt->related_transaction_id,
            ];
        }));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
             Log::error('Attempt to store debt by a non-User instance. User ID: ' . ($user->id ?? 'unknown'));
            return response()->json(['message' => 'Unauthorized operation.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date_format:Y-m-d',
            'deadline' => 'required|date_format:Y-m-d|after_or_equal:date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        try {
            $debt = $user->debts()->create([
                'amount' => $validatedData['amount'],
                'date' => Carbon::parse($validatedData['date'])->toDateString(),
                'deadline' => Carbon::parse($validatedData['deadline'])->toDateString(),
                'notes' => $validatedData['notes'] ?? null,
                'status' => Debt::STATUS_PENDING_VERIFICATION,
            ]);

            return response()->json($debt, 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('QueryException in DebtController@store for user ' . $user->id . ': ' . $e->getMessage(), ['sql' => $e->getSql(), 'bindings' => $e->getBindings()]);
            if (str_contains($e->getMessage(), "Unknown column") && str_contains($e->getMessage(), "notes")) {
                 return response()->json(['message' => "Database error: The 'notes' column might be missing or misspelled in the 'debts' table. Please check database schema.", 'error_detail' => $e->getMessage()], 500);
            }
            return response()->json(['message' => 'Could not create debt due to a database error.', 'error_detail' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Generic Exception in DebtController@store for user ' . $user->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Could not create debt due to a server error.'], 500);
        }
    }

    public function show(Debt $debt)
    {
        $user = Auth::user();
        if (!$user instanceof User || $debt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($debt);
    }

    public function update(Request $request, Debt $debt)
    {
        $user = Auth::user();
        if (!$user instanceof User || $debt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($debt->status === Debt::STATUS_VERIFIED_INCOME_RECORDED) {
            return response()->json(['message' => 'Cannot update a debt that has already been verified and recorded as income.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'date' => 'sometimes|required|date_format:Y-m-d',
            'deadline' => 'sometimes|required|date_format:Y-m-d|after_or_equal:'.($request->input('date') ?? $debt->date->format('Y-m-d')),
            'notes' => 'sometimes|nullable|string|max:1000',
            'status' => ['sometimes', 'required', 'string', Rule::in([Debt::STATUS_PENDING_VERIFICATION, Debt::STATUS_CANCELLED])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        if (isset($validatedData['date'])) $validatedData['date'] = Carbon::parse($validatedData['date'])->toDateString();
        if (isset($validatedData['deadline'])) $validatedData['deadline'] = Carbon::parse($validatedData['deadline'])->toDateString();

        try {
            $debt->update($validatedData);
            return response()->json($debt->fresh());
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('QueryException in DebtController@update for debt ' . $debt->id . ': ' . $e->getMessage());
             if (str_contains($e->getMessage(), "Unknown column") && str_contains($e->getMessage(), "notes")) {
                 return response()->json(['message' => "Database error: The 'notes' column might be missing or misspelled in the 'debts' table during update. Please check database schema.", 'error_detail' => $e->getMessage()], 500);
            }
            return response()->json(['message' => 'Could not update debt due to a database error.'], 500);
        } catch (\Exception $e) {
            Log::error('Generic Exception in DebtController@update for debt ' . $debt->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Could not update debt due to a server error.'], 500);
        }
    }

    public function verifyAndRecordIncome(Request $request, Debt $debt)
    {
        $user = Auth::user();

        if (!$user instanceof User || $debt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to verify this debt.'], 403);
        }

        if ($debt->status !== Debt::STATUS_PENDING_VERIFICATION) {
            return response()->json(['message' => 'This debt is not pending verification or has already been processed.'], 422);
        }

        try {
            $lastSequence = Transaction::where('user_id', $user->id)->max('user_sequence_id');
            $transaction = $user->transactions()->create([
                'amount' => $debt->amount,
                'type' => 'Income',
                'status' => 'Done',
                'date' => Carbon::now()->toDateString(),
                'notes' => "Income from verified debt #{$debt->id}. Original notes: " . ($debt->notes ?? 'N/A'),
                'user_sequence_id' => ($lastSequence ?? 0) + 1,
            ]);

            $debt->status = Debt::STATUS_VERIFIED_INCOME_RECORDED;
            $debt->related_transaction_id = $transaction->id;
            $debt->save();

            return response()->json([
                'message' => 'Debt verified and income recorded successfully.',
                'debt' => $debt->fresh()->load('incomeTransaction'),
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in DebtController@verifyAndRecordIncome for debt ' . $debt->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Could not verify debt and record income due to a server error.'], 500);
        }
    }

    public function destroy(Debt $debt)
    {
        $user = Auth::user();
        if (!$user instanceof User || $debt->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($debt->status === Debt::STATUS_VERIFIED_INCOME_RECORDED) {
            return response()->json(['message' => 'Cannot delete a debt that has already been verified and recorded as income. Consider cancelling the related transaction if needed.'], 422);
        }
        
        try {
            $debt->delete();
            return response()->json(['message' => 'Debt deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Exception in DebtController@destroy for debt ' . $debt->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Could not delete debt due to a server error.'], 500);
        }
    }
}
