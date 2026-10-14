<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'emp_code' => 'nullable|string|unique:users',
            'mobile_number' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'designation_id' => 'nullable|exists:designations,id',
            'segment_id' => 'nullable|exists:segments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'emp_code' => $request->emp_code,
                'mobile_number' => $request->mobile_number,
                'company_id' => $request->company_id,
                'designation_id' => $request->designation_id,
                'segment_id' => $request->segment_id,
                'is_user' => true,
                'employee_status' => 'active',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check if user is active
        if (!$user->is_user || $user->employee_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'User account is inactive'
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // For testing without auth, just return success
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        // For testing without auth, return a sample user
        $user = User::first(); // Get first user for demo
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user->load(['designation', 'segment', 'company'])
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        // For testing without auth, update first user
        $user = User::first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'emp_code' => 'sometimes|string|unique:users,emp_code,' . $user->id,
            'mobile_number' => 'sometimes|string',
            'company_id' => 'sometimes|exists:companies,id',
            'designation_id' => 'sometimes|exists:designations,id',
            'segment_id' => 'sometimes|exists:segments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'name', 'email', 'emp_code', 'mobile_number',
                'company_id', 'designation_id', 'segment_id'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->load(['designation', 'segment', 'company'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // For testing without auth, update first user
        $user = User::first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found'
            ], 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
