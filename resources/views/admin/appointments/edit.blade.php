@extends('admin.layouts.app')

@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment ID: ' . $appointment->id)

@section('content')
    <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="investor_id">Investor</label>
            <select name="investor_id" id="investor_id" required>
                <option value="">Select Investor</option>
                @foreach($investors as $investor) {{-- Assume $investors is passed --}}
                    <option value="{{ $investor->id }}" {{ old('investor_id', $appointment->investor_id) == $investor->id ? 'selected' : '' }}>
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
                    <option value="{{ $umkm->id }}" {{ old('umkm_id', $appointment->umkm_id) == $umkm->id ? 'selected' : '' }}>
                        {{ $umkm->name }} ({{ $umkm->umkm_name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="investment_id">Related Investment (Optional)</label>
            <select name="investment_id" id="investment_id">
                <option value="">None</option>
                 @foreach($investments as $investment_item) {{-- Assume $investments is passed (all investments) --}}
                     <option value="{{ $investment_item->id }}" {{ old('investment_id', $appointment->investment_id) == $investment_item->id ? 'selected' : '' }}>
                        ID: {{ $investment_item->id }} (Inv: {{ $investment_item->investor->name ?? 'N/A' }} - UMKM: {{ $investment_item->umkm->umkm_name ?? $investment_item->umkm->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="appointment_details">Appointment Details</label>
            <textarea name="appointment_details" id="appointment_details">{{ old('appointment_details', $appointment->appointment_details) }}</textarea>
        </div>

        <div class="form-group">
            <label for="appointment_time">Appointment Time</label>
            <input type="datetime-local" name="appointment_time" id="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time ? $appointment->appointment_time->format('Y-m-d\TH:i') : '') }}">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="requested" {{ old('status', $appointment->status) == 'requested' ? 'selected' : '' }}>Requested</option>
                <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="rescheduled_by_umkm" {{ old('status', $appointment->status) == 'rescheduled_by_umkm' ? 'selected' : '' }}>Rescheduled by UMKM</option>
                <option value="rescheduled_by_investor" {{ old('status', $appointment->status) == 'rescheduled_by_investor' ? 'selected' : '' }}>Rescheduled by Investor</option>
            </select>
        </div>
         {{-- Custom status update form for admin if needed, separate from user-facing status updates --}}
        {{-- Or, if admin can directly set status, the above select is fine. --}}
        {{-- Example for specific admin action (like confirming for UMKM) --}}
        {{--
        <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" style="margin-top:10px;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status_source" value="admin_umkm_confirm">
            <button type="submit" name="new_status" value="confirmed" class="btn btn-success">Admin: Confirm for UMKM</button>
        </form>
        --}}


        <div class="form-group">
            <label for="contact_method">Contact Method</label>
            <select name="contact_method" id="contact_method" required>
                <option value="whatsapp" {{ old('contact_method', $appointment->contact_method) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="email" {{ old('contact_method', $appointment->contact_method) == 'email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('contact_method', $appointment->contact_method) == 'phone' ? 'selected' : '' }}>Phone Call</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contact_payload">Contact Payload</label>
            <textarea name="contact_payload" id="contact_payload">{{ old('contact_payload', $appointment->contact_payload) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Appointment</button>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
