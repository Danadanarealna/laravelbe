<?php
    
namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            return response()->json($request->user()->only(['id', 'name', 'email']));
        }
        return response()->json(['message' => 'Not an authenticated investor.'], 403);
    }
}
