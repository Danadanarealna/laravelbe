@extends('admin.layouts.app')

@section('title', 'Edit Investor')
@section('page-title', 'Edit Investor: ' . $investor->name)

@section('content')
    <form action="{{ route('admin.investors.update', $investor) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $investor->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $investor->email) }}" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation">
        </div>

        <button type="submit" class="btn btn-primary">Update Investor</button>
        <a href="{{ route('admin.investors.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
