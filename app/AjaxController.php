<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\AssetCategory;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class AjaxController extends Controller
{

    public function getBranchesWithoutScope($companyId){

        return response()->json([
                    'data' => Branch::where('company_id', $companyId)
                        ->select('id', 'name')
                        ->orderBy('name')
                        ->get()
                ]);
    }
    public function getBranches($companyId)
    {
        return response()->json([
            'data' => Branch::visibleToUser(auth()->user())->where('company_id', $companyId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function getLocations($branchId)
    {

       $locations = BranchLocation::where('branch_id', $branchId)
        ->select('id', 'location_name')
        ->orderBy('location_name')
        ->get();

    return response()->json([
        'status' => true,
        'count'  => $locations->count(),
        'data'   => $locations
    ]);
    }

    public function getAssets($branchId){

         return response()->json([
            'data' => Asset::where('branch_id', $branchId)
        ->select('id', 'asset_name','serial_no')
        ->orderBy('asset_name')
        ->get()
        ]);

    }

    public function getSubCategories($category)
    {
        return response()->json([
            'data' => AssetCategory::where('parent_id', $category)
                ->select('id', 'category_name')
                ->orderBy('category_name')
                ->get()
        ]);
    }

    public function getAssetTypes($category)
    {
        return response()->json([
            'data' => AssetType::where('sub_category_id', $category)
                ->select('id', 'asset_type_name')
                ->orderBy('asset_type_name')
                ->get()
        ]);
    }


    public function getBrancheswithoutScopeByCompanies(Request $request){
        
        $branches = Branch::whereIn('company_id', $request->company_ids)
        ->with('company:id,name')
        ->get()
        ->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'company_id' => $branch->company_id,
                'company_name' => $branch->company->name
            ];
        });

         return response()->json([
        'success' => true,
        'data' => $branches
        ]);
    }

    public function getBranchesByCompanies(Request $request){

         $branches = Branch::visibleToUser(auth()->user())->whereIn('company_id', $request->company_ids)
        ->with('company:id,name')
        ->get()
        ->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'company_id' => $branch->company_id,
                'company_name' => $branch->company->name
            ];
        });


         return response()->json([
        'success' => true,
        'data' => $branches
        ]);

    }

    public function getUsers($branchId){

         $users=User::whereHas('branches', function ($q) use ($branchId) {
                $q->where('branches.id', $branchId);
            })->Where('employee_status', 'active')->get();

        return response()->json([
            'data' => $users
        ]);

    }
    /**
     * Active departments from master for a visible branch.
     * The branch must be visible to the user; department list follows master + role scope.
     */
    public function getDepartmentsByBranch($branchId)
    {
        $branchKey = (int) $branchId;
        $branchQuery = Branch::query()->active()->whereKey($branchKey);
        $user = auth()->user();
        if ($user && ! $user->hasRole('superadmin')) {
            $branchQuery->visibleToUser($user);
        }
        if (! $branchQuery->exists()) {
            abort(404);
        }

        $query = Department::query()
            ->active()
            ->orderBy('department_name');

        if ($user && ! $user->hasRole('superadmin')) {
            $query->visibleToUser($user);
        }

        $departments = $query->get(['id', 'department_name']);

        return response()->json([
            'data' => $departments,
        ]);
    }

    /**
     * All rows from Settings → Employees (same scope as employees index: users except system user).
     */
    public function getSettingsEmployees()
    {
        $users = User::query()
            ->where('id', '!=', 1)
            ->orderBy('name')
            ->with(['departments:id,department_name'])
            ->get(['id', 'name', 'emp_code']);

        return response()->json([
            'data' => $users,
        ]);
    }

}
