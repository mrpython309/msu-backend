<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssetNameRequest;
use App\Models\AssetName;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AssetNamesController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $limit = min(max((int) $request->query('limit', 100), 1), 500);

        $assetNames = AssetName::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%");
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $this->success($assetNames);
    }

    public function store(AssetNameRequest $request)
    {
        $name = (string) ($request->validated()['name'] ?? '');
        $normalized = strtolower(trim($name));
        $existing = AssetName::query()->where('name_normalized', $normalized)->first();
        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->status = 'active';
                $existing->save();
            }

            return $this->success($existing, 'Asset Name already exists');
        }

        $assetName = AssetName::query()->create([
            'name' => $name,
            'status' => $request->validated()['status'] ?? 'active',
        ]);

        return $this->success($assetName, 'Asset Name created successfully', 201);
    }

    public function show(string $id)
    {
        $assetName = AssetName::query()->find($id);
        if (! $assetName) {
            return $this->error('Asset Name not found', 404);
        }

        return $this->success($assetName);
    }

    public function update(AssetNameRequest $request, string $id)
    {
        $assetName = AssetName::query()->find($id);
        if (! $assetName) {
            return $this->error('Asset Name not found', 404);
        }

        $assetName->update([
            'name' => $request->validated()['name'],
            'status' => $request->validated()['status'] ?? $assetName->status,
        ]);

        return $this->success($assetName, 'Asset Name updated successfully');
    }

    public function destroy(string $id)
    {
        $assetName = AssetName::query()->find($id);
        if (! $assetName) {
            return $this->error('Asset Name not found', 404);
        }

        $assetName->delete();

        return $this->success('Asset Name deleted successfully');
    }
}

