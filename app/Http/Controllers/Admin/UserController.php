<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'umkm_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:30',
            'umkm_description' => 'nullable|string',
            'umkm_profile_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_investable' => 'nullable|boolean',
            'is_admin' => 'nullable|boolean',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['is_investable'] = $request->has('is_investable');
        $validatedData['is_admin'] = $request->has('is_admin');

        if ($request->hasFile('umkm_profile_image_path')) {
            $path = $request->file('umkm_profile_image_path')->store('users/profile_images', 'public');
            $validatedData['umkm_profile_image_path'] = $path;
        }

        User::create($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'UMKM User created successfully.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'umkm_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:30',
            'umkm_description' => 'nullable|string',
            'umkm_profile_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_investable' => 'nullable|boolean',
            'is_admin' => 'nullable|boolean',
        ]);

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $validatedData['is_investable'] = $request->has('is_investable');
        $validatedData['is_admin'] = $request->has('is_admin');

        if ($request->hasFile('umkm_profile_image_path')) {
            if ($user->umkm_profile_image_path) {
                Storage::disk('public')->delete($user->umkm_profile_image_path);
            }
            $path = $request->file('umkm_profile_image_path')->store('users/profile_images', 'public');
            $validatedData['umkm_profile_image_path'] = $path;
        }

        $user->update($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'UMKM User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->umkm_profile_image_path) {
            Storage::disk('public')->delete($user->umkm_profile_image_path);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'UMKM User deleted successfully.');
    }
}
