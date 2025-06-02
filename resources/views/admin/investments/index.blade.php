@extends('admin.layouts.app')

@section('title', 'Investments')
@section('page-title', 'Manage Investments')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.investments.create') }}" class="btn btn-primary">Add New Investment</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Investor</th>
                <th>UMKM</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($investments as $investment)
                <tr>
                    <td>{{ $investment->id }}</td>
                    <td>{{ $investment->investor->name ?? 'N/A' }}</td>
                    <td>{{ $investment->umkm->umkm_name ?? ($investment->umkm->name ?? 'N/A') }}</td>
                    <td>{{ number_format($investment->amount, 2) }}</td>
                    <td>{{ $investment->investment_date->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($investment->status) }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.investments.edit', $investment) }}" class="btn btn-warning">Edit</a>
                        @if($investment->status == 'pending')
                        <form action="{{ route('admin.investments.confirm', $investment) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to confirm this investment?');">Confirm</button>
                        </form>
                        @endif
                        <form action="{{ route('admin.investments.destroy', $investment) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this investment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No investments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
     @if($investments->hasPages())
        <div class="pagination">
            {{ $investments->links() }}
        </div>
    @endif
@endsection
