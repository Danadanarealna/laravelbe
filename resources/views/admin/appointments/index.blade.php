@extends('admin.layouts.app')

@section('title', 'Appointments')
@section('page-title', 'Manage Appointments')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">Add New Appointment</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Investor</th>
                <th>UMKM</th>
                <th>Appointment Time</th>
                <th>Status</th>
                <th>Contact Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->id }}</td>
                    <td>{{ $appointment->investor->name ?? 'N/A' }}</td>
                    <td>{{ $appointment->umkm->umkm_name ?? ($appointment->umkm->name ?? 'N/A') }}</td>
                    <td>{{ $appointment->appointment_time ? $appointment->appointment_time->format('Y-m-d H:i') : 'Not Set' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</td>
                    <td>{{ ucfirst($appointment->contact_method) }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-warning">Edit/Update Status</a>
                        <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No appointments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($appointments->hasPages())
        <div class="pagination">
            {{ $appointments->links() }}
        </div>
    @endif
@endsection
