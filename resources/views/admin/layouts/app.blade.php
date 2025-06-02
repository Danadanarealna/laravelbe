<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; padding-top: 20px; position: fixed; height: 100%; overflow-y: auto; }
        .sidebar h2 { text-align: center; margin-bottom: 20px; font-size: 1.5rem; }
        .sidebar ul { list-style-type: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; color: #c2c7d0; padding: 12px 20px; text-decoration: none; font-size: 0.95rem; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: #494e54; color: #fff; }
        .sidebar ul li.nav-group-title { padding: 10px 20px; font-size: 0.8rem; color: #888; text-transform: uppercase; margin-top: 15px; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); background-color: #fff; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 1.8rem; }
        .header .user-info form { margin: 0; }
        .header .user-info button { background: none; border: none; color: #007bff; cursor: pointer; font-size: 1rem; }
        .header .user-info button:hover { text-decoration: underline; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        table th { background-color: #e9ecef; }
        .btn { padding: 8px 15px; margin: 2px; border-radius: 4px; text-decoration: none; display: inline-block; font-size: 0.9rem; }
        .btn-primary { background-color: #007bff; color: white; border: 1px solid #007bff;}
        .btn-primary:hover { background-color: #0056b3; }
        .btn-info { background-color: #17a2b8; color: white; border: 1px solid #17a2b8;}
        .btn-warning { background-color: #ffc107; color: #212529; border: 1px solid #ffc107;}
        .btn-danger { background-color: #dc3545; color: white; border: 1px solid #dc3545;}
        .btn-success { background-color: #28a745; color: white; border: 1px solid #28a745;}
        .action-buttons form { display: inline-block; margin: 0; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group input[type="datetime-local"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-sizing: border-box;
        }
        .form-group textarea { min-height: 100px; }
        .form-group input[type="checkbox"] { width: auto; margin-right: 0.5rem; }
        .alert { padding: 1rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
        .pagination { margin-top: 20px; }
        .pagination span, .pagination a { padding: 8px 12px; margin: 2px; border: 1px solid #ddd; text-decoration: none; color: #007bff; }
        .pagination .active span { background-color: #007bff; color: white; border-color: #007bff; }
        .pagination .disabled span { color: #ccc; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li class="nav-group-title">User Management</li>
            <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">UMKM Users</a></li>
            <li><a href="{{ route('admin.investors.index') }}" class="{{ request()->routeIs('admin.investors.*') ? 'active' : '' }}">Investors</a></li>
            <li class="nav-group-title">Financials</li>
            <li><a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">Transactions</a></li>
            <li><a href="{{ route('admin.investments.index') }}" class="{{ request()->routeIs('admin.investments.*') ? 'active' : '' }}">Investments</a></li>
            <li><a href="{{ route('admin.debts.index') }}" class="{{ request()->routeIs('admin.debts.*') ? 'active' : '' }}">Debts</a></li>
            <li class="nav-group-title">Interactions</li>
            <li><a href="{{ route('admin.appointments.index') }}" class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">Appointments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>@yield('page-title', 'Dashboard')</h1>
            <div class="user-info">
                @auth
                    <span>Welcome, {{ Auth::user()->name }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                @endauth
            </div>
        </header>
        <div class="content">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any() && !request()->routeIs('admin.login.form'))
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
