<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f3f4f6; margin: 0; }
        .login-container { background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-container h1 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; box-sizing: border-box; }
        .form-group input:focus { border-color: #007bff; outline: none; }
        .btn { display: block; width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 1rem; text-align: center; }
        .btn:hover { background-color: #0056b3; }
        .errors { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; }
        .errors ul { margin: 0; padding-left: 1.25rem; }
        .login-link { text-align: center; margin-top: 1.5rem; }
        .login-link a { color: #007bff; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn">Login</button>

            <div class="login-link">
            <p>Don't have an admin account? <a href="{{ route('admin.register.form') }}">Register here</a></p>
            </div>
        </form>
    </div>
</body>
</html>
