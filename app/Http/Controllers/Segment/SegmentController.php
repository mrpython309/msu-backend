<?php

namespace App\Http\Controllers\Segment;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function index(Request $request)
    {
        $segments = Segment::orderBy('name')
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%");
            })
            ->paginate(15);

        $activeSegments = Segment::active()->count();
        $inactiveSegments = Segment::inactive()->count();

        return view('segments.index', compact('segments', 'activeSegments', 'inactiveSegments'));
    }

    public function create()
    {
        return view('segments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:segments,code|max:10|regex:/^[A-Z0-9]+$/',
            'name' => 'required|unique:segments,name|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        Segment::create($validated);

        return redirect()->route('segments.index')
            ->with('success', 'Segment created successfully!');
    }

    public function show(Segment $segment)
    {
        return view('segments.show', compact('segment'));
    }

    public function edit(Segment $segment)
    {
        return view('segments.edit', compact('segment'));
    }

    public function update(Request $request, Segment $segment)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:segments,code,' . $segment->id,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $segment->update($validated);

        return redirect()->route('segments.index')
            ->with('success', 'Segment updated successfully.');
    }

    public function destroy(Segment $segment)
    {
        if ($segment->subsegments()->count() > 0) {
            return back()->with('error', 'Cannot delete segment used in sub segments.');
        }

        $segment->delete();

        return redirect()->route('segments.index')
            ->with('success', 'Segment deleted successfully.');
    }
}
