@extends('admin.layouts.app')

@section('title', 'Transactions')
@section('page-title', 'Manage Transactions')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">Add New Transaction</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>UMKM User</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->user->name ?? 'N/A' }} ({{ $transaction->user->umkm_name ?? 'User Deleted' }})</td>
                    <td>{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->type }}</td>
                    <td>{{ $transaction->payment_method ?? 'N/A' }}</td>
                    <td>{{ $transaction->status }}</td>
                    <td>{{ $transaction->date->format('Y-m-d') }}</td>
                    <td>{{ Str::limit($transaction->notes, 30) }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.transactions.edit', $transaction) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($transactions->hasPages())
        <div class="pagination">
            {{ $transactions->links() }}
        </div>
    @endif
@endsection
