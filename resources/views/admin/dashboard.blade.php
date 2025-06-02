@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
    <style>
        .stat-card-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; flex: 1; min-width: 200px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; }
        .stat-card h3 { margin-top: 0; color: #555; font-size: 1.1rem; }
        .stat-card p { font-size: 2rem; font-weight: bold; color: #007bff; margin-bottom: 0; }
        .recent-activity { margin-top: 30px; }
        .recent-activity h2 { font-size: 1.5rem; margin-bottom: 15px; color: #333; }
        .recent-list { list-style: none; padding: 0; }
        .recent-list li { background-color: #f9f9f9; padding: 10px 15px; border-radius: 4px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .recent-list li .date { font-size: 0.85rem; color: #777; }
    </style>

    <div class="stat-card-container">
        <div class="stat-card">
            <h3>Total UMKM Users</h3>
            <p>{{ $stats['totalUmkmUsers'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Investors</h3>
            <p>{{ $stats['totalInvestors'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Transactions</h3>
            <p>{{ $stats['totalTransactions'] ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Investments</h3>
            <p>{{ $stats['totalInvestments'] ?? 0 }}</p>
        </div>
         <div class="stat-card">
            <h3>Pending Investments</h3>
            <p>{{ $stats['pendingInvestments'] ?? 0 }}</p>
        </div>
    </div>

    <div class="recent-activity">
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <h2>Recent UMKM Registrations</h2>
                @if($recentUmkm->count() > 0)
                    <ul class="recent-list">
                        @foreach($recentUmkm as $umkm)
                            <li>
                                <span>{{ $umkm->name }} ({{ $umkm->umkm_name ?? 'N/A' }})</span>
                                <span class="date">{{ $umkm->created_at->format('d M Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>No recent UMKM registrations.</p>
                @endif
            </div>
            <div style="flex: 1;">
                <h2>Recent Investor Registrations</h2>
                 @if($recentInvestors->count() > 0)
                    <ul class="recent-list">
                        @foreach($recentInvestors as $investor)
                            <li>
                                <span>{{ $investor->name }}</span>
                                <span class="date">{{ $investor->created_at->format('d M Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>No recent investor registrations.</p>
                @endif
            </div>
        </div>
    </div>
    <p>Welcome to the admin panel. Use the sidebar to navigate and manage different aspects of the application.</p>
@endsection
