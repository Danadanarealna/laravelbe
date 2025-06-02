@extends('admin.layouts.app')

@section('title', 'Add Debt Record')
@section('page-title', 'Add New Debt Record')

@section('content')
    <form action="{{ route('admin.debts.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="user_id">UMKM User</label>
            <select name="user_id" id="user_id" required>
                <option value="">Select UMKM User</option>
                @foreach($users as $user) {{-- Assume $users is passed --}}
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->umkm_name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="date">Date Incurred</label>
            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
            <label for="deadline">Deadline for Repayment</label>
            <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}" required>
        </div>

        <div class="form-group">
            <label for="notes">Notes (Optional)</label>
            <textarea name="notes" id="notes">{{ old('notes') }}</textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="{{ \App\Models\Debt::STATUS_PENDING_VERIFICATION }}" {{ old('status', \App\Models\Debt::STATUS_PENDING_VERIFICATION) == \App\Models\Debt::STATUS_PENDING_VERIFICATION ? 'selected' : '' }}>Pending Verification</option>
                <option value="{{ \App\Models\Debt::STATUS_VERIFIED_INCOME_RECORDED }}" {{ old('status') == \App\Models\Debt::STATUS_VERIFIED_INCOME_RECORDED ? 'selected' : '' }}>Verified & Income Recorded</option>
                <option value="{{ \App\Models\Debt::STATUS_REPAID_BY_UMKM }}" {{ old('status') == \App\Models\Debt::STATUS_REPAID_BY_UMKM ? 'selected' : '' }}>Repaid by UMKM</option>
                <option value="{{ \App\Models\Debt::STATUS_CANCELLED }}" {{ old('status') == \App\Models\Debt::STATUS_CANCELLED ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        {{-- related_transaction_id is usually set programmatically, not by admin directly on create --}}

        <button type="submit" class="btn btn-primary">Create Debt Record</button>
        <a href="{{ route('admin.debts.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
