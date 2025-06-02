@extends('admin.layouts.app')

@section('title', 'Add Appointment')
@section('page-title', 'Add New Appointment')

@section('content')
    <form action="{{ route('admin.appointments.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="investor_id">Investor</label>
            <select name="investor_id" id="investor_id" required>
                <option value="">Select Investor</option>
                @foreach($investors as $investor) {{-- Assume $investors is passed --}}
                    <option value="{{ $investor->id }}" {{ old('investor_id') == $investor->id ? 'selected' : '' }}>
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
                    <option value="{{ $umkm->id }}" {{ old('umkm_id') == $umkm->id ? 'selected' : '' }}>
                        {{ $umkm->name }} ({{ $umkm->umkm_name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="investment_id">Related Investment (Optional)</label>
            <select name="investment_id" id="investment_id">
                <option value="">None</option>
                @foreach($investments as $investment) {{-- Assume $investments is passed --}}
                     <option value="{{ $investment->id }}" {{ old('investment_id') == $investment->id ? 'selected' : '' }}>
                        ID: {{ $investment->id }} (Investor: {{ $investment->investor->name ?? 'N/A' }} - UMKM: {{ $investment->umkm->umkm_name ?? $investment->umkm->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="appointment_details">Appointment Details</label>
            <textarea name="appointment_details" id="appointment_details">{{ old('appointment_details') }}</textarea>
        </div>

        <div class="form-group">
            <label for="appointment_time">Appointment Time (Optional)</label>
            <input type="datetime-local" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="requested" {{ old('status', 'requested') == 'requested' ? 'selected' : '' }}>Requested</option>
                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="rescheduled_by_umkm" {{ old('status') == 'rescheduled_by_umkm' ? 'selected' : '' }}>Rescheduled by UMKM</option>
                <option value="rescheduled_by_investor" {{ old('status') == 'rescheduled_by_investor' ? 'selected' : '' }}>Rescheduled by Investor</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contact_method">Contact Method</label>
            <select name="contact_method" id="contact_method" required>
                <option value="whatsapp" {{ old('contact_method', 'whatsapp') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="email" {{ old('contact_method') == 'email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('contact_method') == 'phone' ? 'selected' : '' }}>Phone Call</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contact_payload">Contact Payload (e.g., message, email content)</label>
            <textarea name="contact_payload" id="contact_payload">{{ old('contact_payload') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Appointment</button>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
