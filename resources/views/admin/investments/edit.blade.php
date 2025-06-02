@extends('admin.layouts.app')

@section('title', 'Edit Investment')
@section('page-title', 'Edit Investment ID: ' . $investment->id)

@section('content')
    <form action="{{ route('admin.investments.update', $investment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="investor_id">Investor</label>
            <select name="investor_id" id="investor_id" required>
                <option value="">Select Investor</option>
                @foreach($investors as $investor) {{-- Assume $investors is passed --}}
                    <option value="{{ $investor->id }}" {{ old('investor_id', $investment->investor_id) == $investor->id ? 'selected' : '' }}>
                        {{ $investor->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="umkm_id">UMKM User</label>
            <select name="umkm_id" id="umkm_id" required>
                <option value="">Select UMKM User</option>
                @foreach($umkm_users as $umkm) {{-- Assume $umkm_users is passed --}}
                    <option value="{{ $umkm->id }}" {{ old('umkm_id', $investment->umkm_id) == $umkm->id ? 'selected' : '' }}>
                        {{ $umkm->name }} ({{ $umkm->umkm_name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $investment->amount) }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="investment_date">Investment Date</label>
            <input type="datetime-local" name="investment_date" id="investment_date" value="{{ old('investment_date', $investment->investment_date->format('Y-m-d\TH:i')) }}" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="pending" {{ old('status', $investment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ old('status', $investment->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ old('status', $investment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $investment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Investment</button>
        <a href="{{ route('admin.investments.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
