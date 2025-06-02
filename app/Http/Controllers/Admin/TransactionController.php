<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    private $allowedTransactionTypes = ['Income', 'Expense', 'DebtIncurred', 'Investment'];
    private $allowedPaymentMethods = ['Cash', 'Credit', 'Bank Transfer', 'E-Wallet', 'Other'];
    private $allowedTransactionStatuses = ['Pending', 'Done', 'Cancelled'];

    public function index()
    {
        $transactions = Transaction::with('user')->orderBy('date', 'desc')->paginate(10);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();
        $transactionTypes = $this->allowedTransactionTypes;
        $paymentMethods = $this->allowedPaymentMethods;
        $statuses = $this->allowedTransactionStatuses;
        return view('admin.transactions.create', compact('users', 'transactionTypes', 'paymentMethods', 'statuses'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'type' => ['required', 'string', Rule::in($this->allowedTransactionTypes)],
            'payment_method' => ['required', 'string', Rule::in($this->allowedPaymentMethods)],
            'status' => ['required', 'string', Rule::in($this->allowedTransactionStatuses)],
            'date' => 'required|date_format:Y-m-d',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validatedData['date'] = Carbon::parse($validatedData['date'])->startOfDay()->toDateTimeString();
        
        $user = User::find($validatedData['user_id']);
        if ($user) {
            $lastSequence = Transaction::where('user_id', $user->id)->max('user_sequence_id');
            $validatedData['user_sequence_id'] = ($lastSequence ?? 0) + 1;
        }

        Transaction::create($validatedData);

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function show(Transaction $transaction)
    {
        return redirect()->route('admin.transactions.edit', $transaction);
    }

    public function edit(Transaction $transaction)
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();
        $transactionTypes = $this->allowedTransactionTypes;
        $paymentMethods = $this->allowedPaymentMethods;
        $statuses = $this->allowedTransactionStatuses;
        return view('admin.transactions.edit', compact('transaction', 'users', 'transactionTypes', 'paymentMethods', 'statuses'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'type' => ['required', 'string', Rule::in($this->allowedTransactionTypes)],
            'payment_method' => ['required', 'string', Rule::in($this->allowedPaymentMethods)],
            'status' => ['required', 'string', Rule::in($this->allowedTransactionStatuses)],
            'date' => 'required|date_format:Y-m-d',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validatedData['date'] = Carbon::parse($validatedData['date'])->startOfDay()->toDateTimeString();
        $transaction->update($validatedData);

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
