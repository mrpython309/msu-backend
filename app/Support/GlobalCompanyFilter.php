<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the effective company for create/edit forms without writing session,
 * so index "All" is preserved when navigating back from show/create/edit.
 */
class GlobalCompanyFilter
{
    public static function resolveFormCompanyId(?int $fallback = null): ?int
    {
        $visible = static::visibleCompaniesQuery();

        $companyId = session('global_company_id') ?: $fallback;

        if ($companyId && (clone $visible)->whereKey($companyId)->exists()) {
            return (int) $companyId;
        }

        $firstId = (clone $visible)->value('id');

        return $firstId ? (int) $firstId : null;
    }

    public static function companyCode(?int $companyId, string $default = 'MSULLP'): string
    {
        if (! $companyId) {
            return $default;
        }

        $company = Company::find($companyId);

        return $company?->code ?: $default;
    }

    /**
     * Active companies the current user is allowed to see in filters / forms.
     */
    public static function visibleCompaniesQuery()
    {
        $query = Company::query()->where('status', 'active')->orderBy('name');

        $user = Auth::user();
        if ($user) {
            $query->visibleToUser($user);
        }

        return $query;
    }
}
