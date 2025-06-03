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
                <th>Image</th>
                <th>Name</th>
                <th>Email</th>
                <th>Investments</th>
                <th>Appointments</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($investors as $investor)
                <tr>
                    <td>{{ $investor->id }}</td>
                    <td>
                        <div class="profile-image-wrapper" style="position: relative; width: 50px; height: 50px;">
                            @if($investor->hasProfileImage())
                                <img src="{{ $investor->getApiImageUrl() }}" 
                                     alt="{{ $investor->name }}" 
                                     class="profile-image"
                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #dee2e6; transition: all 0.3s ease;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                     onload="this.style.border='2px solid #28a745';">
                                <div class="profile-fallback" 
                                     style="display: none; width: 50px; height: 50px; border-radius: 50%; background: #007bff; color: white; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                                    {{ $investor->getInitials() }}
                                </div>
                            @else
                                <div class="profile-fallback" 
                                     style="width: 50px; height: 50px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                                    {{ $investor->getInitials() }}
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>{{ $investor->name }}</td>
                    <td>{{ $investor->email }}</td>
                    <td>{{ $investor->investments()->count() }}</td>
                    <td>{{ $investor->appointments()->count() }}</td>
                    <td>{{ $investor->created_at->format('d M Y, H:i') }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.investors.edit', $investor) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.investors.destroy', $investor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this investor? This might also affect related investments and appointments.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No investors found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($investors->hasPages())
        <div class="pagination">
            {{ $investors->links() }}
        </div>
    @endif

    <style>
        .profile-image-wrapper {
            transition: transform 0.2s ease;
        }
        
        .profile-image-wrapper:hover {
            transform: scale(1.05);
        }
        
        .profile-image {
            transition: all 0.3s ease;
        }
        
        .profile-image:hover {
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        
        .action-buttons form {
            display: inline-block;
            margin-left: 5px;
        }
        
        table td {
            vertical-align: middle;
        }
    </style>
@endsection