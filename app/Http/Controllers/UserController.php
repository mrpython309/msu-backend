<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['designation', 'segment', 'company']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('emp_code', 'like', "%{$search}%");
                });
            }

            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            if ($request->has('designation_id')) {
                $query->where('designation_id', $request->designation_id);
            }

            if ($request->has('segment_id')) {
                $query->where('segment_id', $request->segment_id);
            }

            if ($request->has('employee_status')) {
                $query->where('employee_status', $request->employee_status);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'emp_code' => 'nullable|string|unique:users',
            'mile_id' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'designation_id' => 'nullable|exists:designations,id',
            'segment_id' => 'nullable|exists:segments,id',
            'mobile_number' => 'nullable|string',
            'date_of_joining' => 'nullable|date',
            'employee_status' => 'nullable|in:active,inactive,terminated',
            'department_id' => 'nullable|array',
            'dealer_location_id' => 'nullable|array',
            'subsegment_id' => 'nullable|array',
            'reporting_to' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->all();
            $userData['password'] = Hash::make($request->password);
            $userData['is_user'] = true;
            $userData['employee_status'] = $request->employee_status ?? 'active';

            $user = User::create($userData);
            $user->load(['designation', 'segment', 'company']);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        try {
            $user = User::with(['designation', 'segment', 'company'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'emp_code' => 'sometimes|string|unique:users,emp_code,' . $id,
                'mile_id' => 'sometimes|string',
                'company_id' => 'sometimes|exists:companies,id',
                'designation_id' => 'sometimes|exists:designations,id',
                'segment_id' => 'sometimes|exists:segments,id',
                'mobile_number' => 'sometimes|string',
                'date_of_joining' => 'sometimes|date',
                'employee_status' => 'sometimes|in:active,inactive,terminated',
                'left_date' => 'sometimes|date|nullable',
                'department_id' => 'sometimes|array',
                'dealer_location_id' => 'sometimes|array',
                'subsegment_id' => 'sometimes|array',
                'reporting_to' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->except(['password']);
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $user->load(['designation', 'segment', 'company']);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate user
     */
    public function activate($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'employee_status' => 'active',
                'is_user' => true
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User activated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate user
     */
    public function deactivate($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'employee_status' => 'inactive',
                'is_user' => false
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User deactivated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users by company
     */
    public function getUsersByCompany($companyId)
    {
        try {
            $users = User::with(['designation', 'segment'])
                         ->where('company_id', $companyId)
                         ->where('employee_status', 'active')
                         ->get();

            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('employee_status', 'active')->count(),
                'inactive_users' => User::where('employee_status', 'inactive')->count(),
                'terminated_users' => User::where('employee_status', 'terminated')->count(),
                'users_by_company' => User::selectRaw('company_id, COUNT(*) as count')
                                          ->with('company:id,name')
                                          ->groupBy('company_id')
                                          ->get(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
