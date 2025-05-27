<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'password' => 'required|string|min:6|confirmed',
                'umkm_name' => 'sometimes|nullable|string|max:255',
                'contact' => 'sometimes|nullable|string|max:255', // Using 'contact'
                // 'is_investable' is not set during registration, defaults to false
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'umkm_name' => $validated['umkm_name'] ?? null,
                'contact' => $validated['contact'] ?? null,
                // 'is_investable' will use its database default (false)
            ]);

            $token = $user->createToken('auth_token_umkm', ['role:umkm'])->plainTextToken;

            return response()->json([
                'message' => 'UMKM User registered successfully',
                'user' => $user, // Will include is_investable due to model changes
                'token' => $token,
                'token_type' => 'Bearer',
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
            
            /** @var User $user */
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('auth_token_umkm', ['role:umkm'])->plainTextToken;

            return response()->json([
                'message' => 'UMKM User logged in successfully',
                'user' => $user, // Will include is_investable
                'token' => $token,
                'token_type' => 'Bearer',
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
            /** @var User|null $user */
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
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user instanceof User) {
            return response()->json($user); // Will include is_investable
        }
        return response()->json(['message' => 'Not an authenticated UMKM user.'], 401);
    }

    public function updateProfile(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user || !$user instanceof User) {
                 return response()->json(['message' => 'User not authenticated or invalid user type.'], 403);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'umkm_name' => 'sometimes|nullable|string|max:255',
                'contact' => 'sometimes|nullable|string|max:255', // Using 'contact'
                'is_investable' => 'sometimes|boolean', // Validate as boolean
            ]);

            $user->update($validatedData);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh() // Return the updated user model, will include is_investable
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating UMKM profile: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Profile update failed due to a server error.'], 500);
        }
    }
}
