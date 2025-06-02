<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'umkm_name' => 'sometimes|nullable|string|max:255',
                'contact' => 'sometimes|nullable|string|max:30',
                'is_investable' => 'sometimes|in:true,false,0,1',
            ]);

            $isInvestable = false;
            if ($request->has('is_investable')) {
                $isInvestableInput = $request->input('is_investable');
                if (in_array($isInvestableInput, ['true', '1'])) {
                    $isInvestable = true;
                } elseif (in_array($isInvestableInput, ['false', '0'])) {
                    $isInvestable = false;
                }
                 else if (is_bool($isInvestableInput)){
                    $isInvestable = $isInvestableInput;
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'umkm_name' => $validated['umkm_name'] ?? null,
                'contact' => $validated['contact'] ?? null,
                'is_investable' => $isInvestable,
            ]);

            $token = $user->createToken('auth_token_umkm', ['role:umkm'])->plainTextToken;

            return response()->json([
                'message' => 'UMKM User registered successfully',
                'user' => $user->refresh(),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_type' => 'umkm',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error during UMKM registration: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Registration failed due to a server error.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            
            $user = Auth::user();
            if (!($user instanceof User)) {
                 return response()->json(['message' => 'User type mismatch.'], 403);
            }
            $user->tokens()->delete();
            $token = $user->createToken('auth_token_umkm', ['role:umkm'])->plainTextToken;

            return response()->json([
                'message' => 'UMKM User logged in successfully',
                'user' => $user->refresh(),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_type' => 'umkm',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Login failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error during UMKM login: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Login failed due to a server error.'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user && $user instanceof User) {
                $user->currentAccessToken()->delete();
                return response()->json(['message' => 'Successfully logged out']);
            }
            return response()->json(['message' => 'Unauthorized or no active session to logout.'], 401);
        } catch (\Exception $e) {
            Log::error('Error during UMKM logout: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Logout failed due to a server error.'], 500);
        }
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        if ($user && $user instanceof User) {
            return response()->json($user);
        }
        return response()->json(['message' => 'Not an authenticated UMKM user.'], 401);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user instanceof User) {
                 return response()->json(['message' => 'User not authenticated or invalid user type.'], 403);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes','string','email','max:255', Rule::unique('users')->ignore($user->id)],
                'umkm_name' => 'sometimes|nullable|string|max:255',
                'contact' => 'sometimes|nullable|string|max:30',
                'is_investable' => ['sometimes', Rule::in(['true', 'false', '1', '0', true, false])],
                'umkm_description' => 'sometimes|nullable|string|max:5000',
                'umkm_profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            $updateInput = $request->except(['umkm_profile_image', '_method', 'is_investable']);

            if ($request->has('is_investable')) {
                $isInvestableInput = $request->input('is_investable');
                if (is_string($isInvestableInput)) {
                    if (strtolower($isInvestableInput) === 'true' || $isInvestableInput === '1') {
                        $updateInput['is_investable'] = true;
                    } elseif (strtolower($isInvestableInput) === 'false' || $isInvestableInput === '0') {
                        $updateInput['is_investable'] = false;
                    } else {
                        $updateInput['is_investable'] = filter_var($isInvestableInput, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                         if ($updateInput['is_investable'] === null && $request->filled('is_investable')) {
                            throw ValidationException::withMessages(['is_investable' => 'The is_investable field must be a valid boolean representation (true, false, 1, 0).']);
                        }
                    }
                } elseif (is_bool($isInvestableInput)) {
                     $updateInput['is_investable'] = $isInvestableInput;
                } elseif (is_numeric($isInvestableInput) && ($isInvestableInput == 1 || $isInvestableInput == 0)) {
                     $updateInput['is_investable'] = (bool)$isInvestableInput;
                }
            }

            if ($request->hasFile('umkm_profile_image')) {
                if ($user->umkm_profile_image_path) {
                    Storage::disk('public')->delete($user->umkm_profile_image_path);
                }
                $path = $request->file('umkm_profile_image')->store('users/' . $user->id . '/profile_images', 'public');
                $updateInput['umkm_profile_image_path'] = $path;
            }
    
            $user->update($updateInput);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating UMKM profile for user ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Profile update failed due to a server error.'], 500);
        }
    }
}
