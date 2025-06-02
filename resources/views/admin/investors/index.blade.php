@extends('admin.layouts.app')

@section('title', 'Investors')
@section('page-title', 'Manage Investors')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.investors.create') }}" class="btn btn-primary">Add New Investor</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($investors as $investor)
                <tr>
                    <td>{{ $investor->id }}</td>
                    <td>{{ $investor->name }}</td>
                    <td>{{ $investor->email }}</td>
                    <td>{{ $investor->created_at->format('d M Y, H:i') }}</td>
                    <td class="action-buttons">
                        {{-- <a href="{{ route('admin.investors.show', $investor) }}" class="btn btn-info">View</a> --}}
                        <a href="{{ route('admin.investors.edit', $investor) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.investors.destroy', $investor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this investor? This might also affect related investments and appointments.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No investors found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($investors->hasPages())
        <div class="pagination">
            {{ $investors->links() }}
        </div>
    @endif
@endsection
