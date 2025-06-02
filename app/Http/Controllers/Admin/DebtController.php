<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class DebtController extends Controller
{
    public function index()
    {
        $debts = Debt::with('user')->orderBy('deadline', 'asc')->paginate(10);
        return view('admin.debts.index', compact('debts'));
    }

    public function create()
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.debts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date_format:Y-m-d',
            'deadline' => 'required|date_format:Y-m-d|after_or_equal:date',
            'notes' => 'nullable|string|max:1000',
            'status' => ['required', 'string', Rule::in(array_keys(Debt::getStatuses()))],
        ]);
        $validatedData['date'] = Carbon::parse($validatedData['date'])->toDateString();
        $validatedData['deadline'] = Carbon::parse($validatedData['deadline'])->toDateString();

        Debt::create($validatedData);
        return redirect()->route('admin.debts.index')->with('success', 'Debt record created successfully.');
    }

    public function show(Debt $debt)
    {
        return redirect()->route('admin.debts.edit', $debt);
    }

    public function edit(Debt $debt)
    {
        $users = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.debts.edit', compact('debt', 'users'));
    }

    public function update(Request $request, Debt $debt)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date_format:Y-m-d',
            'deadline' => 'required|date_format:Y-m-d|after_or_equal:date',
            'notes' => 'nullable|string|max:1000',
            'status' => ['required', 'string', Rule::in(array_keys(Debt::getStatuses()))],
        ]);
        $validatedData['date'] = Carbon::parse($validatedData['date'])->toDateString();
        $validatedData['deadline'] = Carbon::parse($validatedData['deadline'])->toDateString();

        $debt->update($validatedData);
        return redirect()->route('admin.debts.index')->with('success', 'Debt record updated successfully.');
    }

    public function destroy(Debt $debt)
    {
        if ($debt->status === Debt::STATUS_VERIFIED_INCOME_RECORDED && $debt->related_transaction_id) {
             return redirect()->route('admin.debts.index')->with('error', 'Cannot delete debt that has a recorded income transaction. Cancel the transaction first if needed.');
        }
        $debt->delete();
        return redirect()->route('admin.debts.index')->with('success', 'Debt record deleted successfully.');
    }

    public function verify(Debt $debt)
    {
        if ($debt->status !== Debt::STATUS_PENDING_VERIFICATION) {
            return redirect()->route('admin.debts.index')->with('error', 'Debt is not pending verification or already processed.');
        }

        $user = $debt->user;
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

        return redirect()->route('admin.debts.index')->with('success', 'Debt verified and income recorded successfully.');
    }
}
