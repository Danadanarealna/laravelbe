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
use Illuminate\Support\Str;

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

    /**
     * Get UMKM profile (GET endpoint)
     */
    public function getProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user instanceof User) {
            return response()->json(['message' => 'Not an authenticated UMKM user.'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'umkm_name' => $user->umkm_name,
            'contact' => $user->contact,
            'is_investable' => $user->is_investable,
            'umkm_description' => $user->umkm_description,
            'umkm_profile_image_url' => $user->umkm_profile_image_url,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user instanceof User) {
                return response()->json(['message' => 'User not authenticated or invalid user type.'], 403);
            }

            // Log incoming request for debugging
            Log::info('Profile update request', [
                'user_id' => $user->id,
                'fields' => $request->except(['umkm_profile_image', 'password']),
                'has_image' => $request->hasFile('umkm_profile_image'),
                'files' => $request->allFiles(),
            ]);

            // Updated validation rules - more permissive
            $rules = [
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'umkm_name' => 'sometimes|nullable|string|max:255',
                'contact' => 'sometimes|nullable|string|max:50',
                'is_investable' => 'sometimes|nullable',
                'umkm_description' => 'sometimes|nullable|string|max:5000',
                'umkm_profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ];

            $validatedData = $request->validate($rules);
            
            Log::info('Validation passed', ['validated_data' => array_keys($validatedData)]);

            // Prepare update data
            $updateData = [];

            // Handle basic fields
            if ($request->has('name') && $request->filled('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('email') && $request->filled('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->has('umkm_name')) {
                $updateData['umkm_name'] = $request->umkm_name;
            }

            if ($request->has('contact')) {
                $updateData['contact'] = $request->contact;
            }

            if ($request->has('umkm_description')) {
                $updateData['umkm_description'] = $request->umkm_description;
            }

            // Handle is_investable - be very flexible
            if ($request->has('is_investable')) {
                $isInvestableInput = $request->input('is_investable');
                Log::info('Processing is_investable', ['input' => $isInvestableInput, 'type' => gettype($isInvestableInput)]);
                
                if (is_string($isInvestableInput)) {
                    $updateData['is_investable'] = in_array(strtolower($isInvestableInput), ['true', '1', 'yes']);
                } elseif (is_bool($isInvestableInput)) {
                    $updateData['is_investable'] = $isInvestableInput;
                } elseif (is_numeric($isInvestableInput)) {
                    $updateData['is_investable'] = (bool)$isInvestableInput;
                } else {
                    $updateData['is_investable'] = false;
                }
            }

            // Handle image upload
            if ($request->hasFile('umkm_profile_image')) {
                Log::info('Processing image upload');
                
                try {
                    // Delete old image if exists
                    if ($user->umkm_profile_image_path && Storage::disk('public')->exists($user->umkm_profile_image_path)) {
                        Storage::disk('public')->delete($user->umkm_profile_image_path);
                        Log::info('Deleted old image', ['path' => $user->umkm_profile_image_path]);
                    }

                    // Store new image
                    $image = $request->file('umkm_profile_image');
                    $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $imagePath = "users/{$user->id}/profile_images/{$imageName}";
                    
                    // Ensure directory exists
                    $directory = "users/{$user->id}/profile_images";
                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }
                    
                    // Store the image
                    $stored = Storage::disk('public')->putFileAs(
                        $directory,
                        $image,
                        $imageName
                    );

                    if ($stored) {
                        $updateData['umkm_profile_image_path'] = $imagePath;
                        Log::info('Image stored successfully', ['path' => $imagePath]);
                    } else {
                        Log::error('Failed to store image');
                        return response()->json([
                            'message' => 'Failed to store image file'
                        ], 500);
                    }
                } catch (\Exception $imageException) {
                    Log::error('Image upload exception', ['error' => $imageException->getMessage()]);
                    return response()->json([
                        'message' => 'Error uploading image: ' . $imageException->getMessage()
                    ], 500);
                }
            }

            Log::info('Updating user with data', ['update_data' => $updateData]);

            // Update the user
            $user->update($updateData);

            Log::info('User updated successfully');

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['umkm_profile_image', 'password'])
            ]);
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating UMKM profile for user ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['umkm_profile_image', 'password'])
            ]);
            return response()->json([
                'message' => 'Profile update failed due to a server error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}