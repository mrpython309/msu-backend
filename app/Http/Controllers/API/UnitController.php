<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\UnitRequest;
use App\Models\Unit;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Unit::latest()->get());
    }

    public function store(UnitRequest $request): JsonResponse
    {
        $unit = Unit::create($request->validated());

        return $this->success($unit);
    }

    public function show(string $id)
    {
        return $this->success(Unit::findOrFail($id));
    }

    public function update(UnitRequest $request, string $id)
    {
        $unit = Unit::find($id);

        if (! $unit) {
            return $this->error('Unit not found');
        }

        $unit->update($request->validated());

        return $this->success($unit);
    }

    public function destroy(string $id)
    {
        $unit = Unit::find($id);

        if (! $unit) {
            return $this->error('Unit not found');
        }

        if ($unit->items()->exists()) {
            return $this->error('Cannot delete unit linked to item masters.');
        }

        $unit->delete();

        return $this->success('Unit deleted successfully');
    }
}
