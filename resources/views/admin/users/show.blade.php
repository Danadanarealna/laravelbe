@extends('admin.layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details: ' . $user->name)

@section('content')
    <div class="user-details">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to Users</a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit User</a>
        </div>

        <div class="profile-section" style="margin-bottom: 30px;">
            @if($user->umkm_profile_image_path)
                <img src="{{ $user->getAdminImageUrl() }}" alt="{{ $user->umkm_name ?? $user->name }}" width="150" height="150" style="border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
            @else
                <div style="width: 150px; height: 150px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #495057; font-size: 48px; margin-bottom: 20px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
        </div>

        <div class="details-section">
            <h3>User Information</h3>
            <table>
                <tr>
                    <td><strong>ID:</strong></td>
                    <td>{{ $user->id }}</td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <td><strong>UMKM Name:</strong></td>
                    <td>{{ $user->umkm_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Contact:</strong></td>
                    <td>{{ $user->contact ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Is Investable:</strong></td>
                    <td>{{ $user->is_investable ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td><strong>Is Admin:</strong></td>
                    <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td><strong>Created At:</strong></td>
                    <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Updated At:</strong></td>
                    <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                </tr>
            </table>

            @if($user->umkm_description)
                <div style="margin-top: 20px;">
                    <h4>UMKM Description</h4>
                    <p>{{ $user->umkm_description }}</p>
                </div>
            @endif
        </div>

        <div style="margin-top: 30px;">
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
        </div>
    </div>
@endsection