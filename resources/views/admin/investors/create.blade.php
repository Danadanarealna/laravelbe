@extends('admin.layouts.app')

@section('title', 'Add Investor')
@section('page-title', 'Add New Investor')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.investors.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="profile_image">Profile Image</label>
                    <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                    @error('profile_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF (max 2MB)</small>
                </div>

                <button type="submit" class="btn btn-primary">Create Investor</button>
                <a href="{{ route('admin.investors.index') }}" class="btn btn-info">Cancel</a>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Preview</h5>
                </div>
                <div class="card-body text-center">
                    <div id="profile-preview" 
                         style="width: 120px; height: 120px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 36px; margin: 0 auto 15px auto; border: 3px dashed #dee2e6;">
                        ?
                    </div>
                    <h6 id="name-preview" class="text-muted">Enter name to preview</h6>
                    <p id="email-preview" class="text-muted small">Enter email to preview</p>
                </div>
            </div>

            <!-- Image preview card -->
            <div class="card mt-3" id="image-preview-card" style="display: none;">
                <div class="card-header">
                    <h5>Image Preview</h5>
                </div>
                <div class="card-body text-center">
                    <img id="preview-image" 
                         class="img-fluid rounded-circle" 
                         style="width: 120px; height: 120px; object-fit: cover;">
                    <p class="text-muted small mt-2">This will be the profile image</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time preview updates
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const namePreview = document.getElementById('name-preview');
            const emailPreview = document.getElementById('email-preview');
            const profilePreview = document.getElementById('profile-preview');
            
            function updatePreview() {
                const name = nameInput.value.trim();
                namePreview.textContent = name || 'Enter name to preview';
                
                const email = emailInput.value.trim();
                emailPreview.textContent = email || 'Enter email to preview';
                
                if (name) {
                    profilePreview.textContent = name[0].toUpperCase();
                    profilePreview.style.background = '#007bff';
                    profilePreview.style.color = 'white';
                } else {
                    profilePreview.textContent = '?';
                    profilePreview.style.background = '#e9ecef';
                    profilePreview.style.color = '#6c757d';
                }
            }
            
            nameInput.addEventListener('input', updatePreview);
            emailInput.addEventListener('input', updatePreview);
        });

        function previewImage(input) {
            const preview = document.getElementById('preview-image');
            const previewCard = document.getElementById('image-preview-card');
            const profilePreview = document.getElementById('profile-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewCard.style.display = 'block';
                    
                    // Update main profile preview too
                    profilePreview.style.backgroundImage = `url(${e.target.result})`;
                    profilePreview.style.backgroundSize = 'cover';
                    profilePreview.style.backgroundPosition = 'center';
                    profilePreview.textContent = '';
                    profilePreview.style.borderStyle = 'solid';
                    profilePreview.style.borderColor = '#28a745';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                previewCard.style.display = 'none';
                profilePreview.style.backgroundImage = '';
                profilePreview.style.borderStyle = 'dashed';
                profilePreview.style.borderColor = '#dee2e6';
                
                // Reset to initial
                const nameInput = document.getElementById('name');
                if (nameInput.value.trim()) {
                    profilePreview.textContent = nameInput.value.trim()[0].toUpperCase();
                } else {
                    profilePreview.textContent = '?';
                }
            }
        }
    </script>

    <style>
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        #profile-preview {
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@endsection