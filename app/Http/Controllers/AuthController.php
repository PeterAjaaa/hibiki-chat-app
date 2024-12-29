<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Let the user register and return the user instance and their JWT access token
    // TODO: Add email verification on register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error during registration process',
                'error' => [
                    'error_details' => $validator->errors()
                ]
            ], 422);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);
        }
    }

    // Let the user log in using their credentials
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error during login process',
                'error' => [
                    'error_details' => $validator->errors()
                ]
            ], 422);
        } else {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials provided during login process',
                    'error' => [
                        'error_details' => 'Invalid credentials provided during login process'
                    ]
                ], 401);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'token' => $token
                    ]
                ], 200);
            }
        }
    }

    // Log out the user by invalidating their JWT access token
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ], 200);
    }

    // Get authenticated user's details
    public function getAuthenticatedUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'success' => true,
                'message' => 'User found successfully',
                'data' => [
                    'user' => $user
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => [
                    'error_details' => $e->getMessage()
                ]
            ], 404);
        }
    }

    // Refresh current user JWT access token
    public function refreshAccessToken()
    {
        try {
            $newToken = auth('api')->refresh(false, true);
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => [
                    'error_details' => $e->getMessage()
                ]
            ], 401);
        }
    }
}
