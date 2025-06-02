<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InvestorController extends Controller
{
    public function index()
    {
        $investors = Investor::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.investors.index', compact('investors'));
    }

    public function create()
    {
        return view('admin.investors.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:investors',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        Investor::create($validatedData);

        return redirect()->route('admin.investors.index')->with('success', 'Investor created successfully.');
    }

    public function show(Investor $investor)
    {
        return redirect()->route('admin.investors.edit', $investor);
    }

    public function edit(Investor $investor)
    {
        return view('admin.investors.edit', compact('investor'));
    }

    public function update(Request $request, Investor $investor)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('investors')->ignore($investor->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $investor->update($validatedData);
        return redirect()->route('admin.investors.index')->with('success', 'Investor updated successfully.');
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return redirect()->route('admin.investors.index')->with('success', 'Investor deleted successfully.');
    }
}
