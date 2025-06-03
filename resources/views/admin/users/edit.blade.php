@extends('admin.layouts.app')

@section('title', 'Edit UMKM User')
@section('page-title', 'Edit User: ' . $user->name)

@section('content')
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation">
        </div>

        <div class="form-group">
            <label for="umkm_name">UMKM Name</label>
            <input type="text" name="umkm_name" id="umkm_name" value="{{ old('umkm_name', $user->umkm_name) }}">
        </div>

        <div class="form-group">
            <label for="contact">Contact (Phone/WhatsApp)</label>
            <input type="text" name="contact" id="contact" value="{{ old('contact', $user->contact) }}">
        </div>

        <div class="form-group">
            <label for="umkm_description">UMKM Description</label>
            <textarea name="umkm_description" id="umkm_description">{{ old('umkm_description', $user->umkm_description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="umkm_profile_image_path">UMKM Profile Image (leave blank to keep current)</label>
            <input type="file" name="umkm_profile_image_path" id="umkm_profile_image_path">
            @if($user->umkm_profile_image_path)
                <img src="{{ $user->getAdminImageUrl() }}" alt="{{ $user->umkm_name }}" width="100" style="margin-top:10px;">
            @endif
        </div>

        <div class="form-group">
            <label for="is_investable">
                <input type="checkbox" name="is_investable" id="is_investable" value="1" {{ old('is_investable', $user->is_investable) ? 'checked' : '' }}>
                Is Investable?
            </label>
        </div>

        <div class="form-group">
            <label for="is_admin">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                Is Admin?
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection