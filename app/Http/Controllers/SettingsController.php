<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Settings;
use App\Models\BranchLocation;
use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies=Company::visibleToUser(auth()->user())->active()->get();      

        $settings = Settings::all();
        foreach ($settings as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        $branches=isset($settings['warehouse_company_id']) && $settings['warehouse_company_id'] > 0 ? Branch::Where('company_id',$settings['warehouse_company_id'])->get() : [];

        $branchLocations=isset($settings['warehouse_id']) && $settings['warehouse_id'] > 0 ? BranchLocation::Where('branch_id',$settings['warehouse_id'])->Where('status','active')->get() : [];

        return view('settings',compact('branches','settings','branchLocations','companies'));
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
        'warehouse_id' => 'required|exists:branches,id',

        'warehouse_location' => [
            'required',
            Rule::exists('branch_locations', 'id')->where(function ($q) use ($request) {
                $q->where('branch_id', $request->warehouse_id);
            }),
        ],

        'pagination_size' => 'required|integer|min:1',
    ]);

    // ✅ Only allow required keys (VERY IMPORTANT)
    $allowedKeys = [
        'warehouse_company_id',
        'warehouse_id',
        'warehouse_location',
        'pagination_size'
    ];

    foreach ($allowedKeys as $key) {

        if (!$request->has($key)) {
            continue;
        }

        Settings::updateOrCreate(
            ['key' => $key],
            ['value' => $request->$key]
        );

        // 🔥 CLEAR CACHE
        Cache::forget("setting.$key");
    }

       

        return back()->with('success','Settings updated successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
