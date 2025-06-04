@extends('admin.layouts.app')

@section('title', 'UMKM Users')
@section('page-title', 'Manage UMKM Users')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add New UMKM User</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Email</th>
                <th>UMKM Name</th>
                <th>Contact</th>
                <th>Investable</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        @if($user->hasUmkmProfileImage() && $user->umkm_profile_image_url)
                            <img src="{{ $user->umkm_profile_image_url }}" alt="{{ $user->umkm_name ?? $user->name }}" width="50" height="50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #cccccc; box-sizing: border-box;">
                        @else
                            <div style="width: 50px; height: 50px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #495057; border: 2px solid #cccccc; box-sizing: border-box;">
                                {{ $user->getUmkmInitials() }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->umkm_name ?? 'N/A' }}</td>
                    <td>{{ $user->contact ?? 'N/A' }}</td>
                    <td>{{ $user->is_investable ? 'Yes' : 'No' }}</td>
                    <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No UMKM users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
        <div class="pagination">
            {{ $users->links() }}
        </div>
    @endif
@endsection
