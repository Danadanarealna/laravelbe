@extends('admin.layouts.app')

@section('title', 'Edit Investor')
@section('page-title', 'Edit Investor: ' . $investor->name)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.investors.update', $investor) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $investor->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $investor->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep current password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave blank to keep current password</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm new password if changing">
                </div>

                <div class="form-group">
                    <label for="profile_image">Profile Image (leave blank to keep current)</label>
                    <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                    @error('profile_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave blank to keep current image. Supported formats: JPG, PNG, GIF (max 2MB)</small>
                </div>

                <button type="submit" class="btn btn-primary">Update Investor</button>
                <a href="{{ route('admin.investors.index') }}" class="btn btn-info">Cancel</a>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Current Profile</h5>
                </div>
                <div class="card-body text-center">
                    <div class="profile-image-container mb-3">
                        @if($investor->hasProfileImage())
                            <img src="{{ $investor->getApiImageUrl() }}" 
                                 alt="{{ $investor->name }}" 
                                 id="current-profile-image"
                                 class="img-fluid rounded-circle"
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div id="profile-fallback" 
                                 style="display: none; width: 120px; height: 120px; border-radius: 50%; background: #007bff; color: white; align-items: center; justify-content: center; font-weight: bold; font-size: 36px; margin: 0 auto;">
                                {{ $investor->getInitials() }}
                            </div>
                        @else
                            <div id="profile-fallback" 
                                 style="width: 120px; height: 120px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 36px; margin: 0 auto;">
                                {{ $investor->getInitials() }}
                            </div>
                        @endif
                    </div>

                    <h5>{{ $investor->name }}</h5>
                    <p class="text-muted">{{ $investor->email }}</p>
                    
                    <div class="stats mt-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <strong>{{ $investor->investments()->count() }}</strong>
                                <br><small class="text-muted">Investments</small>
                            </div>
                            <div class="col-6">
                                <strong>{{ $investor->appointments()->count() }}</strong>
                                <br><small class="text-muted">Appointments</small>
                            </div>
                        </div>
                    </div>

                    <div class="info mt-3">
                        <small class="text-muted">
                            <strong>Joined:</strong> {{ $investor->created_at->format('M d, Y') }}<br>
                            <strong>Last Updated:</strong> {{ $investor->updated_at->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- New image preview card -->
            <div class="card mt-3" id="new-image-preview" style="display: none;">
                <div class="card-header">
                    <h5>New Image Preview</h5>
                </div>
                <div class="card-body text-center">
                    <img id="preview-image" 
                         class="img-fluid rounded-circle" 
                         style="width: 120px; height: 120px; object-fit: cover;">
                    <p class="text-muted small mt-2">This will replace the current image</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview-image');
            const previewContainer = document.getElementById('new-image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    
                    // Scroll to preview
                    previewContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        }

        // Test current image URL on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentImage = document.getElementById('current-profile-image');
            
            if (currentImage) {
                console.log('Testing current profile image URL:', currentImage.src);
                
                currentImage.addEventListener('load', function() {
                    console.log('✅ Current profile image loaded successfully');
                    this.style.borderColor = '#28a745';
                });
                
                currentImage.addEventListener('error', function() {
                    console.error('❌ Current profile image failed to load:', this.src);
                    this.style.borderColor = '#dc3545';
                });
            }
        });
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
        
        #current-profile-image {
            transition: all 0.3s ease;
        }
        
        #current-profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }
    </style>
@endsection