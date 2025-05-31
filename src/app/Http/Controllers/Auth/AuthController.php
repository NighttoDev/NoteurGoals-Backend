<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password_hash' => 'required|string|min:8',
            'registration_type' => 'required|in:email,google,facebook'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'display_name' => $request->display_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'registration_type' => $request->registration_type,
            'status' => 'active'
        ]);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'is_premium' => false
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password_hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password_hash, $user->password_hash)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        $resetToken = Str::random(60);
        
        $user->update([
            'reset_token' => $resetToken
        ]);

        // TODO: Send reset password email

        return response()->json([
            'message' => 'Password reset link sent to your email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('reset_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid reset token'
            ], 400);
        }

        $user->update([
            'password_hash' => Hash::make($request->password),
            'reset_token' => null
        ]);

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
} 