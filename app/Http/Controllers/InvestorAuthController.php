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
        
        // Ensure profile_image_url is included (it will be null for new registration)
        $investorData = $investor->toArray(); // This will include appended attributes like profile_image_url

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_type' => 'investor',
            'user' => $investorData, // Send the full investor data with appended URL
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

        // Convert investor model to array to ensure appended attributes are included
        $investorData = $investor->toArray(); 

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_type' => 'investor',
            'user' => $investorData, // Send the full investor data with appended URL
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
        $user = $request->user();
        if ($user instanceof Investor) {
            // When returning the user, toArray() will ensure $appends are processed.
            return response()->json($user->toArray()); 
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
            'profile_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Added webp, 2MB max
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

            $image = $request->file('profile_image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            // Ensure the path is consistent, e.g., "investors/1/profile_images/random.jpg"
            $imagePath = "investors/{$investor->id}/profile_images/{$imageName}"; 
            
            Storage::disk('public')->put($imagePath, file_get_contents($image));

            $investor->profile_image_path = $imagePath;
        } elseif ($request->exists('profile_image') && is_null($request->input('profile_image'))) {
            // If 'profile_image' is explicitly sent as null, consider it a request to remove the image.
            if ($investor->profile_image_path && Storage::disk('public')->exists($investor->profile_image_path)) {
                Storage::disk('public')->delete($investor->profile_image_path);
            }
            $investor->profile_image_path = null;
        }


        if ($request->filled('name')) {
            $investor->name = $request->name;
        }

        if ($request->filled('email') && $request->email !== $investor->email) {
            $investor->email = $request->email;
        }

        if ($request->filled('password')) {
            $investor->password = Hash::make($request->password);
        }

        $investor->save();
        
        // Refresh model to get latest data including any changes by mutators/accessors
        $investor->refresh(); 
        $investorData = $investor->toArray(); // This will include the appended profile_image_url

        return response()->json([
            'message' => 'Profile updated successfully',
            'investor' => $investorData,
        ]);
    }
}
