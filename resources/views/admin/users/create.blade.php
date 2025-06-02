@extends('admin.layouts.app')

@section('title', 'Add UMKM User')
@section('page-title', 'Add New UMKM User')

@section('content')
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>

        <div class="form-group">
            <label for="umkm_name">UMKM Name</label>
            <input type="text" name="umkm_name" id="umkm_name" value="{{ old('umkm_name') }}">
        </div>

        <div class="form-group">
            <label for="contact">Contact (Phone/WhatsApp)</label>
            <input type="text" name="contact" id="contact" value="{{ old('contact') }}">
        </div>

        <div class="form-group">
            <label for="umkm_description">UMKM Description</label>
            <textarea name="umkm_description" id="umkm_description">{{ old('umkm_description') }}</textarea>
        </div>

        <div class="form-group">
            <label for="umkm_profile_image_path">UMKM Profile Image</label>
            <input type="file" name="umkm_profile_image_path" id="umkm_profile_image_path">
        </div>

        <div class="form-group">
            <label for="is_investable">
                <input type="checkbox" name="is_investable" id="is_investable" value="1" {{ old('is_investable') ? 'checked' : '' }}>
                Is Investable?
            </label>
        </div>

        <div class="form-group">
            <label for="is_admin">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                Is Admin?
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
