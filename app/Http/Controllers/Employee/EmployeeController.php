<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DealerLocation;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Segment;
use App\Models\SubSegment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        // $employees = User::where('id', '!=','1')->get();
        $employees = User::with([
            'designation',
            'company',
            'segment',
        ])
            ->where('id', '!=', 1)
            ->get();

        return view('employee.index', compact('employees'));
    }

    public function create()
    {
        $departments  = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $segments     = Segment::where('status', 'active')->get();
        $users        = User::with('designation')->where('id', '!=', 1)->get();
        $roles        = Role::where('id', '!=', 1)->get();
        $companies    = Company::where('status', 'active')->get();

        return view(
            'employee.create',
            compact('users', 'departments', 'designations', 'segments', 'roles', 'companies')
        );
    }

    public function store(Request $request)
    {
        // --------------------------------------------------
        // 1. BASE VALIDATION (always required)
        // --------------------------------------------------
        $rules = [
            'emp_code'         => 'required|string|max:50|unique:users,emp_code',
            'mile_id'          => 'required|string|max:50',
            'employee_name'    => 'required|string|max:255',
            'date_of_joining'  => 'required|date',
            'mobile_number'    => 'required|digits:10',
            'email'            => 'required|email|unique:users,email',
            'designation_id'   => 'required|exists:designations,id',

            'reporting_status' => 'required|boolean',

            'is_user'          => 'nullable|in:yes,no',
            'role_id'          => 'nullable|exists:roles,id',
            'password'         => 'nullable|min:8',
            'employee_status'      => 'required|in:active,inactive'
        ];

        // --------------------------------------------------
        // 2. IF reporting_status = OFF (0) → FULL VALIDATION
        // --------------------------------------------------
        if ($request->reporting_status == 0) {

            $rules = array_merge($rules, [
            
                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|exists:segments,id',

                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',

                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',

                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',

                'reporting_to'         => 'nullable|array',
                'reporting_to.*'       => 'exists:users,id',
            ]);
        }


        $validated = $request->validate($rules);


        $user = User::create([
            'emp_code'           => $validated['emp_code'],
            'mile_id'            => $validated['mile_id'],
            'name'               => $validated['employee_name'],
            'email'              => $validated['email'],
            'designation_id'     => $validated['designation_id'],
            'date_of_joining'    => $validated['date_of_joining'],
            'mobile_number'      => $validated['mobile_number'],

            'reporting_status'   => $request->boolean('reporting_status'),
            'employee_status'    => $validated['employee_status'] ?? null,

            'company_id'         => $validated['company_id'] ?? null,
            'segment_id'         => $validated['segment_id'] ?? null,

            // JSON / array columns
            'department_id'      => $validated['department_id'] ?? null,
            'dealer_location_id' => $validated['dealer_location_id'] ?? null,
            'subsegment_id'      => $validated['subsegment_id'] ?? null,
            'reporting_to'       => $validated['reporting_to'] ?? null,

            'is_user'            => $request->is_user === 'yes' ? 'yes' : 'no',

            'password'           => ($request->is_user === 'yes' && $request->password)
                ? Hash::make($request->password)
                : null,
        ]);


        if ($request->is_user === 'yes' && $request->role_id) {
            $role = Role::find($request->role_id);

            if ($role) {
                $user->syncRoles([$role->name]);
                $user->update(['role_id' => $request->role_id]);
            }
        }

        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee created successfully');
    }

    public function edit(User $employee)
    {
        $departments  = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $segments     = Segment::where('status', 'active')->get();
        $users        = User::with('designation')->where('id', '!=', 1)->get();
        $roles        = Role::where('id', '!=', 1)->get();
        $companies    = Company::where('status', 'active')->get();

        return view(
            'employee.edit',
            compact('employee', 'users', 'departments', 'designations', 'segments', 'roles', 'companies')
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);


        $rules = [
            'emp_code'         => 'required|string|max:50|unique:users,emp_code,' . $user->id,
            'mile_id'          => 'required|string|max:50',
            'employee_name'    => 'required|string|max:255',
            'date_of_joining'  => 'required|date',
            'mobile_number'    => 'required|digits:10',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'designation_id'   => 'required|exists:designations,id',

            'reporting_status' => 'required|boolean',

            'is_user'          => 'nullable|in:yes,no',
            'role_id'          => 'nullable|exists:roles,id',
            'password'         => 'nullable|min:8',
            'employee_status'      => 'required|in:active,inactive'
        ];



        if ($request->reporting_status == 0) {

            $rules = array_merge($rules, [

                'company_id'           => 'required|exists:companies,id',
                'segment_id'           => 'required|exists:segments,id',

                'department_id'        => 'required|array',
                'department_id.*'      => 'exists:departments,id',

                'dealer_location_id'   => 'required|array',
                'dealer_location_id.*' => 'exists:dealer_locations,id',

                'subsegment_id'        => 'required|array',
                'subsegment_id.*'      => 'exists:sub_segments,id',

                'reporting_to'         => 'nullable|array',
                'reporting_to.*'       => 'exists:users,id',
            ]);
        }


        $validated = $request->validate($rules);


        $user->update([
            'emp_code'           => $validated['emp_code'],
            'mile_id'            => $validated['mile_id'],
            'name'               => $validated['employee_name'],
            'email'              => $validated['email'],
            'designation_id'     => $validated['designation_id'],
            'date_of_joining'    => $validated['date_of_joining'],
            'mobile_number'      => $validated['mobile_number'],

            'reporting_status'   => $request->boolean('reporting_status'),
            'employee_status'    => $validated['employee_status'] ?? null,

            'company_id'         => $validated['company_id'] ?? null,
            'segment_id'         => $validated['segment_id'] ?? null,

            // JSON / array fields
            'department_id'      => $validated['department_id'] ?? null,
            'dealer_location_id' => $validated['dealer_location_id'] ?? null,
            'subsegment_id'      => $validated['subsegment_id'] ?? null,
            'reporting_to'       => $validated['reporting_to'] ?? null,

            'is_user'            => $request->is_user === 'yes' ? 'yes' : 'no',

            'password'           => ($request->is_user === 'yes' && $request->password)
                ? Hash::make($request->password)
                : $user->password,
        ]);


        if ($request->is_user === 'yes' && $request->role_id) {

            $role = Role::find($request->role_id);

            if ($role) {
                $user->syncRoles([$role->name]);
                $user->update(['role_id' => $request->role_id]);
            }

        } else {
            // remove roles if not a user
            $user->roles()->detach();
            $user->update(['role_id' => null]);
        }


        return redirect()
            ->route('employee.index')
            ->with('success', 'Employee updated successfully');
    }

    public function getBySegment($segmentId)
    {
        return SubSegment::where('segment_id', $segmentId)
            ->where('status', 'active')
            ->get();
    }

    public function byLocations($company_id)
    {
        return DealerLocation::where('company_id', $company_id)
            ->where('status', 'active')
            ->get();
    }

    // public function byMultipleCompanies(Request $request)
    // {
    //     $companyIds = $request->company_ids;

    //     $companies = Company::with('dealerLocations')
    //         ->whereIn('id', $companyIds)
    //         ->get();

    //     return response()->json($companies);
    // }

}
