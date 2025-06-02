@extends('admin.layouts.app')

@section('title', 'Add Investor')
@section('page-title', 'Add New Investor')

@section('content')
    <form action="{{ route('admin.investors.store') }}" method="POST">
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

        <button type="submit" class="btn btn-primary">Create Investor</button>
        <a href="{{ route('admin.investors.index') }}" class="btn btn-info">Cancel</a>
    </form>
@endsection
