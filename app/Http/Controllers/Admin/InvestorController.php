<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        // Create investor first to get the ID
        $investor = Investor::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
        ]);

        // Handle profile image upload after investor is created
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = "investors/{$investor->id}/profile_images/{$imageName}";
            
            // Store the image
            Storage::disk('public')->putFileAs(
                "investors/{$investor->id}/profile_images",
                $image,
                $imageName
            );

            // Update investor with image path
            $investor->update(['profile_image_path' => $imagePath]);
        }

        return redirect()->route('admin.investors.index')->with('success', 'Investor created successfully.');
    }

    public function show(Investor $investor)
    {
        return view('admin.investors.edit', compact('investor'));
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($investor->profile_image_path && Storage::disk('public')->exists($investor->profile_image_path)) {
                Storage::disk('public')->delete($investor->profile_image_path);
            }

            // Store new image
            $image = $request->file('profile_image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $imagePath = "investors/{$investor->id}/profile_images/{$imageName}";
            
            // Store the image
            Storage::disk('public')->putFileAs(
                "investors/{$investor->id}/profile_images",
                $image,
                $imageName
            );

            $validatedData['profile_image_path'] = $imagePath;
        }

        $investor->update($validatedData);

        return redirect()->route('admin.investors.index')->with('success', 'Investor updated successfully.');
    }

    public function destroy(Investor $investor)
    {
        // Delete profile image if exists
        if ($investor->profile_image_path && Storage::disk('public')->exists($investor->profile_image_path)) {
            Storage::disk('public')->delete($investor->profile_image_path);
            
            // Also try to delete the directory if it's empty
            $directory = "investors/{$investor->id}/profile_images";
            $files = Storage::disk('public')->files($directory);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory($directory);
                
                // Delete parent directory if empty too
                $parentDirectory = "investors/{$investor->id}";
                $parentFiles = Storage::disk('public')->allFiles($parentDirectory);
                if (empty($parentFiles)) {
                    Storage::disk('public')->deleteDirectory($parentDirectory);
                }
            }
        }

        $investor->delete();
        return redirect()->route('admin.investors.index')->with('success', 'Investor deleted successfully.');
    }
}