<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Investor;
use App\Models\Transaction;
use App\Models\Investment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalUmkmUsers' => User::where('is_admin', false)->count(),
            'totalInvestors' => Investor::count(),
            'totalTransactions' => Transaction::count(),
            'totalInvestments' => Investment::count(),
            'pendingInvestments' => Investment::where('status', 'pending')->count(),
        ];

        $recentUmkm = User::where('is_admin', false)->orderBy('created_at', 'desc')->take(5)->get();
        $recentInvestors = Investor::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUmkm', 'recentInvestors'));
    }
}
