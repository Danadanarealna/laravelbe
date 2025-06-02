<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Investor;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Investment::with(['investor', 'umkm'])->orderBy('investment_date', 'desc')->paginate(10);
        return view('admin.investments.index', compact('investments'));
    }

    public function create()
    {
        $investors = Investor::orderBy('name')->get();
        $umkm_users = User::where('is_admin', false)->where('is_investable', true)->orderBy('umkm_name')->get();
        return view('admin.investments.create', compact('investors', 'umkm_users'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'umkm_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'investment_date' => 'required|date_format:Y-m-d\TH:i',
            'status' => 'required|string|in:pending,active,completed,cancelled',
        ]);
        $validatedData['investment_date'] = Carbon::parse($validatedData['investment_date'])->toDateTimeString();

        Investment::create($validatedData);
        return redirect()->route('admin.investments.index')->with('success', 'Investment created successfully.');
    }

    public function show(Investment $investment)
    {
        return redirect()->route('admin.investments.edit', $investment);
    }

    public function edit(Investment $investment)
    {
        $investors = Investor::orderBy('name')->get();
        $umkm_users = User::where('is_admin', false)->orderBy('umkm_name')->get();
        return view('admin.investments.edit', compact('investment', 'investors', 'umkm_users'));
    }

    public function update(Request $request, Investment $investment)
    {
        $validatedData = $request->validate([
            'investor_id' => 'required|exists:investors,id',
            'umkm_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'investment_date' => 'required|date_format:Y-m-d\TH:i',
            'status' => 'required|string|in:pending,active,completed,cancelled',
        ]);
        $validatedData['investment_date'] = Carbon::parse($validatedData['investment_date'])->toDateTimeString();

        $investment->update($validatedData);
        return redirect()->route('admin.investments.index')->with('success', 'Investment updated successfully.');
    }

    public function destroy(Investment $investment)
    {
        $investment->delete();
        return redirect()->route('admin.investments.index')->with('success', 'Investment deleted successfully.');
    }

    public function confirm(Investment $investment)
    {
        if ($investment->status == 'pending') {
            $investment->status = 'active';
            $investment->save();
            return redirect()->route('admin.investments.index')->with('success', 'Investment confirmed successfully.');
        }
        return redirect()->route('admin.investments.index')->with('error', 'Investment could not be confirmed or was not pending.');
    }
}
