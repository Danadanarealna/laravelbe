@extends('admin.layouts.app')

@section('title', 'Edit Transaction')
@section('page-title', 'Edit Transaction ID: ' . $transaction->id)

@section('content')
    <form action="{{ route('admin.transactions.update', $transaction) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="user_id">UMKM User</label>
            <select name="user_id" id="user_id" required>
                <option value="">Select UMKM User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id', $transaction->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->umkm_name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $transaction->amount) }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="type">Type (Financial Nature)</label>
            <select name="type" id="type" required>
                 @foreach($transactionTypes as $type)
                    <option value="{{ $type }}" {{ old('type', $transaction->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select name="payment_method" id="payment_method" required>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method }}" {{ old('payment_method', $transaction->payment_method) == $method ? 'selected' : '' }}>{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ old('status', $transaction->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
            <label for="notes">Notes (Optional)</label>
            <textarea name="notes" id="notes">{{ old('notes', $transaction->notes) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Transaction</button>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
