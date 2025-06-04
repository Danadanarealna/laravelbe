<?php
    
namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class InvestorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:investors',
            'password' => 'required|string|min:8',
        ]);

        $investor = Investor::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $investor->createToken('auth_token_investor', ['role:investor'])->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_type' => 'investor',
            'user' => $investor->only(['id', 'name', 'email']),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $investor = Investor::where('email', $request->email)->first();

        if (!$investor || !Hash::check($request->password, $investor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records for investors.'],
            ]);
        }

        $token = $investor->createToken('auth_token_investor', ['role:investor'])->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_type' => 'investor',
            'user' => $investor->only(['id', 'name', 'email']),
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user() instanceof Investor) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Successfully logged out']);
        }
        return response()->json(['message' => 'Unauthorized or incorrect user type for this logout.'], 403);
    }

    public function user(Request $request)
    {
        if ($request->user() instanceof Investor) {
            return response()->json($request->user());
        }
        return response()->json(['message' => 'Not an authenticated investor.'], 403);
    }

    /**
     * Update investor profile including profile image
     */
    public function updateProfile(Request $request)
    {
        $investor = $request->user();

        if (!$investor instanceof Investor) {
            return response()->json(['message' => 'Not an authenticated investor.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:investors,email,' . $investor->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

            $investor->profile_image_path = $imagePath;
        }

        // Update other fields
        if ($request->has('name')) {
            $investor->name = $request->name;
        }

        if ($request->has('email')) {
            $investor->email = $request->email;
        }

        if ($request->has('password') && $request->password) {
            $investor->password = Hash::make($request->password);
        }

        $investor->save();

        // FIXED: Prepare response data with proper image URL
        $investorData = $investor->toArray();
        
        // Use the API image URL instead of the storage URL for better CORS handling
        if ($investor->hasProfileImage()) {
            $investorData['profile_image_url'] = $investor->getApiImageUrl();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'investor' => $investorData,
        ]);
    }
}