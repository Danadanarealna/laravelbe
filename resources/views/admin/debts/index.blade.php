@extends('admin.layouts.app')

@section('title', 'Debts')
@section('page-title', 'Manage Debts')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.debts.create') }}" class="btn btn-primary">Add New Debt Record</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>UMKM User</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Related Transaction ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($debts as $debt)
                <tr>
                    <td>{{ $debt->id }}</td>
                    <td>{{ $debt->user->name ?? 'N/A' }} ({{ $debt->user->umkm_name ?? 'User Deleted' }})</td>
                    <td>{{ number_format($debt->amount, 2) }}</td>
                    <td>{{ $debt->date->format('Y-m-d') }}</td>
                    <td>{{ $debt->deadline->format('Y-m-d') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $debt->status)) }}</td>
                    <td>{{ $debt->related_transaction_id ?? 'N/A' }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.debts.edit', $debt) }}" class="btn btn-warning">Edit</a>
                        @if($debt->status == \App\Models\Debt::STATUS_PENDING_VERIFICATION)
                        <form action="{{ route('admin.debts.verify', $debt) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to verify this debt and record income?');">Verify & Record Income</button>
                        </form>
                        @endif
                        <form action="{{ route('admin.debts.destroy', $debt) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this debt record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No debt records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($debts->hasPages())
        <div class="pagination">
            {{ $debts->links() }}
        </div>
    @endif
@endsection
