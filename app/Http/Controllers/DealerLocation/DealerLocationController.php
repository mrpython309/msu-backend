<?php

namespace App\Http\Controllers\DealerLocation;

use App\Http\Controllers\Controller;
use App\Models\DealerLocation;
use App\Models\Company;
use Illuminate\Http\Request;

class DealerLocationController extends Controller
{
    public function index(Request $request) {
        $dealerLocations = DealerLocation::with('company')
            ->when($request->name, fn($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->code, fn($q) => $q->where('code', $request->code))
            ->when($request->company_id, fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(10);

        $companies = Company::visibleToUser(auth()->user())->active()->get();
        return view('dealer-locations.index', compact('dealerLocations', 'companies'));
    }

    public function create() {
      $companies = Company::active()->get();
      return view('dealer-locations.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:dealer_locations',
            'company_id' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        DealerLocation::create($validated);

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location created successfully.');
    }

    public function show(DealerLocation $dealerLocation)
    {
        return view('dealer-locations.show', compact('dealerLocation'));
    }

    public function edit(DealerLocation $dealerLocation)
    {
        $companies = Company::active()->get();
        return view('dealer-locations.edit', compact('dealerLocation', 'companies'));
    }

    public function update(Request $request, DealerLocation $dealerLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:dealer_locations,code,' . $dealerLocation->id,
            'company_id' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $dealerLocation->update($validated);

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location updated successfully.');
    }

    public function destroy(DealerLocation $dealerLocation)
    {
        $dealerLocation->delete();

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location deleted successfully.');
    }
}
