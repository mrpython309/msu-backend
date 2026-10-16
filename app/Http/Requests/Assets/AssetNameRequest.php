<?php

namespace App\Http\Requests\Assets;

use App\Models\AssetName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** Quick-create from asset form resolves duplicates in the controller — don't block here. */
        if ($this->routeIs('asset-names.quick-store')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ];
        }

        $assetNameId = $this->route('asset_name');
        if (is_object($assetNameId)) {
            $assetNameId = $assetNameId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($assetNameId): void {
                    $normalized = strtolower(trim((string) $value));
                    $exists = AssetName::query()
                        ->where('name_normalized', $normalized)
                        ->when($assetNameId, fn ($q) => $q->where('id', '!=', (int) $assetNameId))
                        ->exists();
                    if ($exists) {
                        $fail('The asset name has already been taken.');
                    }
                },
            ],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}

